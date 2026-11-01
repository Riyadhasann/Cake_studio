import * as THREE from 'three';
import { OrbitControls } from 'three/addons/controls/OrbitControls.js';
import { RoomEnvironment } from 'three/addons/environments/RoomEnvironment.js';

/*
 * A round layer cake that looks photographed rather than drawn: per layer a sponge and a cream band whose edges
 * are slightly uneven (a hand-made cake is never a perfect cylinder), a frosted top with spatula swirls, piped
 * rosettes, on the same champagne-gold pedestal stand as the designs, lit by a warm studio light (matte, no shine) with soft shadows and darkening where layers meet.
 * Each layer wears its flavor's picture, or a crumb or cream pattern drawn from its color while none is uploaded.
 *
 * Spec passed to render(): { kg, layers: [{ sponge: Surface, cream: Surface }], design?, message? }, bottom layer first,
 * where Surface = { color, side, scale, top? } (side and top are picture URLs or null, scale is the grain size)
 * and message = { text, color } is piped on the plain cake's top in that icing color (makeWriting()); changing only
 * the message swaps the writing without building the cake again.
 *
 * With a design ({ image, fit, toppings, topImage, frames? }) the outside is that design's photo instead: the photo is projected back onto a
 * cylinder (its front half as photographed, eased in towards the edges, mirrored round the back) and onto the top, on a pedestal stand like the photos, and
 * the cake turns slowly. With a turn of photos (frames: two or more URLs, the cover first, evenly spaced round the cake, each
 * next one showing the side that was on the right) every side wears the photo taken of it instead of a mirror (turnTexture()). fit says where the cake sits in the photo, as shares of its width (x, r) and height
 * (top and bottom: the side's top and bottom edge straight in front; top_curve and bottom_curve: how far those
 * edges curve up towards the sides).
 */

// TUNING. World units: a 2 kg cake has radius 2. The layer heights match the cake photo (97 px sponge, 66 px cream, 973 px wide).
export const SPONGE_H = 0.4;
export const CREAM_H = 0.27;
export const CREAM_BULGE = 0.024; // how far the cream bulges past the sponge, as a share of the radius (more in some places, less in others)
export const CREAM_RIDGE = 0.005; // the fine ridges a palette knife leaves running round the cream, as a share of the radius
export const TOP_H = 0.12; // frosting thickness
export const TOP_ROUND = 0.07; // how much the frosting edge curves down
export const TILE_WORLD = 1.2; // the size of one repeat of a tileable picture at grain size 1
export const BUMP = { sponge: 2.2, cream: 0.35 }; // how bumpy sponge and cream look in the light
export const WOBBLE = { side: 0.007, top: 0.012 }; // unevenness of the side and the top, as a share of the radius
export const EDGE_SHADE = 0.32; // darkening where sponge meets cream (0 = none)
export const CRUMB_PITS = 0.006; // how deep the open crumb on a sponge's side is, as a share of the radius
export const CRUST = 0.45; // how much of a sponge's side shows browned, baked patches (0 = none)
export const GLOW = { sponge: 0.03, cream: 0.07 }; // light that passes through sponge and cream (they are soft, not plastic): lifts their shadows
export const CRUMBS = 2; // loose crumbs on the plate round the foot, per unit of radius (0 = none)
export const WRITING = { width: 1.24, height: 0.8, font: '"Dancing Script", "Brush Script MT", cursive', relief: 6, thick: 0.07, maxSize: 260 }; // the message's area on the top (shares of the radius, inside the rosettes), its piped look, the extra stroke that makes it bolder (share of the letter size) and the biggest letters (px on a 1024 px wide sheet)
export const AUTO_ROTATE = true; // the plain cake turns slowly too (a design always turns); false = a still shot until dragged
export const DESIGN_EDGE = 1.2; // how far round from the front (radians) the photo is read: past it the photo is too foreshortened (and its outline shows background)
export const DESIGN_TRUE = 0.4; // up to this angle from the front the side is exactly the photo; from there to the cake's edge it eases out to DESIGN_EDGE
export const DESIGN_SHADE = 0.15; // how much a design's side darkens as it turns away (the photo has its own light)
export const TOPPING_SINK = 0.08; // how much of a topping's picture sits below the top (it is pressed into the cream)
export const DESIGN_SPIN = 1.6; // turning speed of a design (OrbitControls units: 2 = one turn in 30 s)
export const PLAIN_SPIN = 2; // turning speed of the plain cake (same units; one turn in 30 s)
export const DESIGN_TURN = 1; // which way a turn of photos goes round: 1 = each next photo shows the side that was on the right, -1 = on the left
export const TURN_BLEND = 0.35; // how wide (radians, at most) neighbouring photos of a turn cross-fade into each other
export const CONTACT_SHADE = 0.45; // how dark the soft shadow is where the cake stands on the plate (0 = none)
const SEGMENTS = 192;
const STRIP_RATIO = 6; // wider than this (width / height) is a strip, wrapped once around the cake

const VIEWS = { front: 1.4, angle: 1.13, top: 0.16 }; // camera angle from straight above, in radians
const FALLBACK_COLOR = { sponge: '#e8cfa4', cream: '#fbf1e1' };
// The table scene behind the stage (resources/images/cake-scene.svg, 300 x 400, background-size: cover): the stand's
// foot is kept on its empty spot, y = 298 of 400 (just above the centre of the scene's contact shadow).
const SCENE = { aspect: 300 / 400, footY: 298 / 400 };
// An uploaded background (Shop & pictures) sets its own shape and table spot on the page as CSS variables.
function sceneOfPage() {
    const style = getComputedStyle(document.documentElement);
    const aspect = parseFloat(style.getPropertyValue('--scene-aspect'));
    const footY = parseFloat(style.getPropertyValue('--scene-foot'));

    return { aspect: aspect > 0 ? aspect : SCENE.aspect, footY: footY > 0 && footY < 1 ? footY : SCENE.footY };
}

/** Cake radius for a size: 1 kg 1.8, 2 kg 2.0, 3 kg 2.2, kept within 1.6 to 2.6. */
export function radiusForKg(kg) {
    const weight = Number(kg) || 2;

    return Math.min(2.6, Math.max(1.6, 1.6 + 0.2 * weight));
}

export function createCakeViewer(container) {
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
    renderer.setClearColor(0x000000, 0);
    renderer.outputColorSpace = THREE.SRGBColorSpace;
    renderer.toneMapping = THREE.NeutralToneMapping; // keeps the flavors' real colors (ACES washes them out)
    renderer.toneMappingExposure = 1.0;
    renderer.shadowMap.enabled = true;
    renderer.shadowMap.type = THREE.PCFShadowMap;
    renderer.domElement.style.cssText = 'display:block;width:100%;height:100%;touch-action:none;outline:none;';
    container.appendChild(renderer.domElement);

    const scene = new THREE.Scene();
    const pmrem = new THREE.PMREMGenerator(renderer);
    const room = new RoomEnvironment();
    scene.environment = pmrem.fromScene(room, 0.04).texture;
    scene.environmentIntensity = 0.65;
    room.dispose();
    pmrem.dispose();

    // Studio lighting: a warm key light with soft shadows and a cool fill (no rim light or shine on the cake).
    const key = new THREE.DirectionalLight(0xfff0dc, 2.4);
    key.castShadow = true;
    key.shadow.mapSize.set(2048, 2048);
    key.shadow.radius = 9;
    key.shadow.bias = -0.0004;
    key.shadow.normalBias = 0.02;
    scene.add(key, key.target);
    scene.add(new THREE.HemisphereLight(0xf4f1ff, 0xd9c3a0, 0.55)); // the warm ground color is light bounced off the plate

    const camera = new THREE.PerspectiveCamera(26, 1, 0.1, 100);
    camera.position.setFromSphericalCoords(16, VIEWS.angle, Math.PI / 5);

    const controls = new OrbitControls(camera, renderer.domElement);
    controls.enableDamping = true;
    controls.dampingFactor = 0.08;
    controls.enablePan = false;
    // The plain cake can be dragged up and down too, between the top and the front views (a swipe on it then tilts
    // instead of scrolling the page). A design is held at its photo's height (lockTilt): an up or down swipe scrolls.
    controls.minPolarAngle = VIEWS.top;
    controls.maxPolarAngle = VIEWS.front;
    renderer.domElement.style.touchAction = 'none';
    const lockTilt = (phi) => {
        const free = !shownDesign;
        controls.minPolarAngle = free ? VIEWS.top : phi;
        controls.maxPolarAngle = free ? VIEWS.front : phi;
        renderer.domElement.style.touchAction = free ? 'none' : 'pan-y';
    };
    let spinning = AUTO_ROTATE && !reduceMotion;
    controls.autoRotate = spinning;
    controls.autoRotateSpeed = PLAIN_SPIN;

    // With auto-rotate on: it stops at the first touch and starts again 4 seconds after letting go.
    let resumeTimer = null;
    controls.addEventListener('start', () => {
        controls.autoRotate = false;
        clearTimeout(resumeTimer);
    });
    controls.addEventListener('end', () => {
        if (spinning) resumeTimer = setTimeout(() => { controls.autoRotate = true; }, 4000);
    });

    // Left and right arrow keys turn the cake (OrbitControls would pan, which is off); up and down tilt the plain cake.
    const onKey = (event) => {
        const turn = { ArrowLeft: -1, ArrowRight: 1 }[event.key] ?? 0;
        const tilt = shownDesign ? 0 : ({ ArrowUp: -1, ArrowDown: 1 }[event.key] ?? 0);
        if (!turn && !tilt) return;
        event.preventDefault();
        controls.dispatchEvent({ type: 'start' });
        const offset = camera.position.clone().sub(controls.target);
        const spherical = new THREE.Spherical().setFromVector3(offset);
        spherical.theta += turn * (Math.PI / 12);
        spherical.phi = THREE.MathUtils.clamp(spherical.phi + tilt * 0.12, controls.minPolarAngle, controls.maxPolarAngle);
        camera.position.copy(controls.target).add(new THREE.Vector3().setFromSpherical(spherical));
        controls.dispatchEvent({ type: 'end' });
    };
    container.addEventListener('keydown', onKey);

    // Camera moves (a new view, or a taller or wider cake) ease over a moment instead of jumping.
    let tween = null;
    let fit = { distance: 16, targetY: 1 };
    let phiGoal = VIEWS.angle;

    // theta: turn round to that side too (0 = the front of a design photo), the short way.
    function moveCamera(ms = 700, theta = null) {
        const offset = camera.position.clone().sub(controls.target);
        const from = new THREE.Spherical().setFromVector3(offset);
        const turn = theta === null ? 0 : Math.atan2(Math.sin(theta - from.theta), Math.cos(theta - from.theta));
        // The locked height opens just enough for the move, and locks again at its end.
        controls.minPolarAngle = Math.min(from.phi, phiGoal);
        controls.maxPolarAngle = Math.max(from.phi, phiGoal);
        tween = {
            start: performance.now(),
            ms: reduceMotion ? 0 : ms,
            phi: [from.phi, phiGoal],
            theta: [from.theta, from.theta + turn],
            radius: [from.radius, fit.distance],
            targetY: [controls.target.y, fit.targetY],
        };
    }

    function stepTween(now) {
        if (!tween) return;
        const t = tween.ms ? Math.min(1, (now - tween.start) / tween.ms) : 1;
        const e = 1 - Math.pow(1 - t, 3);
        const lerp = ([a, b]) => a + (b - a) * e;

        controls.target.y = lerp(tween.targetY);
        const spherical = new THREE.Spherical().setFromVector3(camera.position.clone().sub(controls.target));
        spherical.phi = lerp(tween.phi);
        if (tween.theta[0] !== tween.theta[1]) spherical.theta = lerp(tween.theta);
        spherical.radius = lerp(tween.radius);
        camera.position.copy(controls.target).add(new THREE.Vector3().setFromSpherical(spherical));
        if (t >= 1) {
            tween = null;
            lockTilt(phiGoal);
        }
    }

    // Keeps the foot of the stand on the scene's table: the view is shifted up or down so the foot lands on the
    // table's spot, wherever the camera is. Looking down from nearly overhead the foot is hidden under the cake, so
    // the shift fades out there (it would push the cake off the stage).
    let footY = 0;
    const foot = new THREE.Vector3();
    const backdrop = sceneOfPage();
    function standOnTable() {
        camera.clearViewOffset();
        if (!cake) return;
        const w = container.clientWidth;
        const h = container.clientHeight;
        if (!w || !h) return;

        // Where the spot is on the stage: the share of the scene's height that cover shows, then the spot within it.
        const shown = Math.min(1, backdrop.aspect / (w / h));
        const spotNdc = 1 - 2 * ((backdrop.footY - (1 - shown) / 2) / shown);

        camera.updateMatrixWorld();
        const footNdc = foot.set(0, footY, 0).project(camera).y;
        const shift = (spotNdc - footNdc) * smoothstep(0.55, 1.0, controls.getPolarAngle());
        if (Math.abs(shift) > 0.001) camera.setViewOffset(w, h, 0, (shift * h) / 2, w, h);
    }

    // Draw only while the viewer is on screen and the tab is visible.
    let onScreen = true;
    const tick = (now) => {
        stepTween(now);
        controls.update();
        standOnTable();
        renderer.render(scene, camera);
    };
    const updateLoop = () => renderer.setAnimationLoop(onScreen && !document.hidden ? tick : null);
    const visibility = new IntersectionObserver(([entry]) => { onScreen = entry.isIntersecting; updateLoop(); });
    visibility.observe(container);
    document.addEventListener('visibilitychange', updateLoop);

    const resize = () => {
        const width = container.clientWidth;
        const height = container.clientHeight;
        if (!width || !height) return;
        renderer.setSize(width, height, false);
        camera.aspect = width / height;
        camera.updateProjectionMatrix();
        renderer.render(scene, camera); // resizing clears the canvas: draw again now, not a frame later (no flash)
    };
    const resizer = new ResizeObserver(resize);
    resizer.observe(container);
    resize();
    updateLoop();

    let cake = null;
    let renderToken = 0;
    let stillDesign = false; // showFront(): a design held still, its photographed side to the camera
    let shownDesign = null; // the design photo on show, so a new design turns its front to the camera
    let shownShape = null; // everything render() was last given except the message, to tell a message-only change
    let writing = null; // the message on the plain cake's top
    let topOf = null; // { radius, y } of the plain cake's top, where the writing goes

    async function render(spec) {
        const token = ++renderToken;
        const shape = JSON.stringify({ ...spec, message: null });
        if (cake && shape === shownShape) {
            await placeWriting(spec?.message, token); // only the message changed: keep the cake, rewrite its top
            return;
        }
        const layers = spec?.layers ?? [];
        const radius = radiusForKg(spec?.kg);
        const topCream = layers[layers.length - 1]?.cream ?? {};
        const design = spec?.design?.image ? spec.design : null;

        // Every picture this cake needs, loaded once and kept for the next pick.
        const toppings = design?.toppings ?? [];
        const frames = (design?.frames ?? []).length > 1 ? design.frames : [];
        const urls = design
            ? [...new Set([design.image, design.topImage, ...frames, ...toppings.map((topping) => topping.image)].filter(Boolean))]
            : [...new Set(layers.flatMap((layer) => [layer.sponge?.side, layer.cream?.side]).concat(topCream.top).filter(Boolean))];
        const loaded = new Map(await Promise.all(urls.map(async (url) => [url, await loadTexture(url)])));
        if (token !== renderToken) return; // a newer pick arrived while loading

        const photo = design ? loaded.get(design.image) : null;
        // A turn is only used whole: with a photo missing the rest would no longer be evenly spaced.
        const turn = frames.map((url) => loaded.get(url));
        const anisotropy = renderer.capabilities.getMaxAnisotropy();
        const first = !cake;
        const built = photo
            ? buildDesignCake(photo, design.fit, toppings.filter((t) => loaded.get(t.image)).map((t) => ({ ...t, texture: loaded.get(t.image) })), radius, anisotropy, loaded.get(design.topImage), turn.every(Boolean) ? turn : [])
            : buildCake(layers, radius, loaded, anisotropy);
        if (writing) {
            disposeWriting(writing); // its bump picture isn't freed with the cake
            cake?.remove(writing);
            writing = null;
        }
        if (cake) {
            scene.remove(cake);
            disposeGroup(cake);
        }
        cake = built.group;
        footY = built.foot;
        scene.add(cake);
        shownShape = shape;
        topOf = photo ? null : { radius, y: built.height };
        placeWriting(spec?.message, token);

        const height = built.height;

        // A design turns slowly, starting from the side that was photographed, seen from the photo's height.
        const turnToFront = Boolean(photo) && design.image !== shownDesign;
        if (photo) phiGoal = built.viewPhi; // a new fit can change it
        else if (shownDesign) phiGoal = VIEWS.angle; // back to the plain cake's usual angle
        shownDesign = photo ? design.image : null;
        spinning = (photo ? !stillDesign : AUTO_ROTATE) && !reduceMotion;
        clearTimeout(resumeTimer);
        controls.autoRotate = spinning;
        controls.autoRotateSpeed = photo ? DESIGN_SPIN : PLAIN_SPIN;

        // Light from the front left and above; its shadow box just covers the plate.
        key.position.set(-radius * 1.6, height + 11, radius * 2.2);
        key.target.position.set(0, height / 2, 0);
        const box = radius * 1.9;
        Object.assign(key.shadow.camera, { left: -box, right: box, top: box, bottom: -box, near: 1, far: 30 });
        key.shadow.camera.updateProjectionMatrix();

        // Frame the whole cake and its pedestal stand (built.foot is the bottom of the stand).
        // The narrower of the two view angles decides: the stage is taller than wide.
        const halfV = THREE.MathUtils.degToRad(camera.fov / 2);
        const halfH = Math.atan(Math.tan(halfV) * camera.aspect);
        let distance;
        if (photo) {
            // Seen nearly from the side: the stand's plate must fit across, the cake with its toppings and the stand top to bottom.
            const plate = radius * 1.21;
            const across = plate * 1.08 / Math.tan(halfH);
            const tall = ((height + radius * 0.35 - built.foot) / 2) * 1.08 / Math.tan(halfV);
            distance = Math.max(across, tall) + plate;
        } else {
            const reach = Math.hypot(radius * 1.25, (height - built.foot) / 2 + 0.2);
            distance = (reach / Math.sin(Math.min(halfV, halfH))) * 1.08;
        }
        // The toppings stand about a third of the radius above a design's top.
        fit = { distance, targetY: photo ? (height + radius * 0.35 + built.foot) / 2 : (height + built.foot) / 2 };
        controls.minDistance = fit.distance * 0.55;
        controls.maxDistance = fit.distance * 1.6;
        moveCamera(first ? 0 : 600, turnToFront ? 0 : null);
    }

    // Writes the message on the plain cake's top (a design has its own top: nothing is written there).
    async function placeWriting(message, token) {
        const text = topOf && message?.text?.trim() ? message.text.trim() : '';
        if (text) {
            // The script font comes from Google Fonts; wait for it so the first letters aren't drawn in a fallback.
            await document.fonts?.load(`700 120px ${WRITING.font}`).catch(() => null);
            if (token !== renderToken) return;
        }
        if (writing) {
            cake?.remove(writing);
            disposeWriting(writing);
            writing = null;
        }
        if (!text || !cake) return;
        writing = makeWriting(text, message.color, topOf.radius, topOf.y, renderer.capabilities.getMaxAnisotropy());
        cake.add(writing);
    }

    function setView(name) {
        if (shownDesign) return; // a design stays at its photo's height
        phiGoal = VIEWS[name] ?? VIEWS.angle;
        controls.dispatchEvent({ type: 'start' });
        moveCamera();
        controls.dispatchEvent({ type: 'end' });
    }

    // Turns a design's photographed side to the camera and holds it there (still = true), or lets it turn again.
    function showFront(still = true) {
        stillDesign = still;
        spinning = (shownDesign ? !still : AUTO_ROTATE) && !reduceMotion;
        clearTimeout(resumeTimer);
        controls.autoRotate = spinning;
        if (still) moveCamera(600, 0);
    }

    function dispose() {
        renderer.setAnimationLoop(null);
        clearTimeout(resumeTimer);
        visibility.disconnect();
        resizer.disconnect();
        document.removeEventListener('visibilitychange', updateLoop);
        container.removeEventListener('keydown', onKey);
        controls.dispose();
        if (cake) disposeGroup(cake);
        scene.environment?.dispose();
        renderer.dispose();
        renderer.domElement.remove();
    }

    return { render, setView, showFront, dispose };
}

/** A plain cake from its layers: { group, height, foot } (foot: the bottom of its stand). */
function buildCake(layers, radius, loaded, anisotropy) {
    const group = new THREE.Group();
    const circumference = 2 * Math.PI * radius;
    const side = (kind, surface, height, offset = 0) => sideMaterial(kind, surface, loaded.get(surface?.side), height, circumference, anisotropy, offset);
    const add = (mesh) => {
        mesh.castShadow = true;
        mesh.receiveShadow = true;
        group.add(mesh);
        return mesh;
    };
    let y = 0;

    layers.forEach((layer, index) => {
        // The pictures start at a different place on each layer, so they don't line up.
        add(new THREE.Mesh(spongeGeometry(radius, SPONGE_H, y), side('sponge', layer.sponge, SPONGE_H, index * 0.37))).position.y = y;
        y += SPONGE_H;

        add(new THREE.Mesh(creamGeometry(radius, CREAM_H, y), side('cream', layer.cream, CREAM_H, index * 0.61))).position.y = y;
        y += CREAM_H;
    });

    // Frosting on top, in the top layer's cream: a rounded edge, a swirled top with its top picture, piped rosettes.
    const topCream = layers[layers.length - 1]?.cream ?? {};
    add(new THREE.Mesh(rimGeometry(radius, TOP_H, TOP_ROUND, y), side('cream', topCream, TOP_H))).position.y = y;
    add(new THREE.Mesh(topGeometry(radius - TOP_ROUND, radius), topMaterial(topCream, loaded, radius, anisotropy))).position.y = y + TOP_H;

    const creamTone = averageColor(loaded.get(topCream.top) ?? loaded.get(topCream.side), topCream.color || FALLBACK_COLOR.cream);
    group.add(makeRosettes(radius, y + TOP_H, creamTone));

    // A few crumbs fallen on the plate, in the bottom sponge's color.
    const bottomSponge = layers[0]?.sponge ?? {};
    if (CRUMBS > 0) group.add(makeCrumbs(radius, averageColor(loaded.get(bottomSponge.side), bottomSponge.color || FALLBACK_COLOR.sponge)));

    // The same pedestal stand as the designs.
    const foot = addStand(group, radius);

    return { group, height: y + TOP_H, foot };
}

/** Stands a cake (sitting at y = 0) on the champagne-gold pedestal, with a soft shadow under its foot. Returns the foot's height. */
function addStand(group, radius) {
    const stand = standGeometry(radius);
    const mesh = new THREE.Mesh(stand.geometry, new THREE.MeshPhysicalMaterial({
        color: 0xcbbf9f, metalness: 0.6, roughness: 0.32, clearcoat: 0.6, clearcoatRoughness: 0.2, envMapIntensity: 1.1,
    }));
    mesh.receiveShadow = true; // the cake's shadow falls on the plate
    group.add(mesh);

    const contact = new THREE.Mesh(
        new THREE.PlaneGeometry(radius * 1.4, radius * 1.4),
        new THREE.MeshBasicMaterial({ map: shadowTexture(), transparent: true, depthWrite: false }),
    );
    contact.rotation.x = -Math.PI / 2;
    contact.position.y = stand.foot + 0.002;
    group.add(contact);

    // Where the cake stands on the plate little light gets in: a soft dark band just round its foot (under the cake it is hidden).
    const occlusion = new THREE.Mesh(
        new THREE.PlaneGeometry(radius * 2.4, radius * 2.4),
        new THREE.MeshBasicMaterial({ map: occlusionTexture(), transparent: true, depthWrite: false, polygonOffset: true, polygonOffsetFactor: -2 }),
    );
    occlusion.rotation.x = -Math.PI / 2;
    occlusion.position.y = 0.004;
    group.add(occlusion);

    return stand.foot;
}

/** Where a cake sits in a design photo when the admin hasn't fitted it: the bundled photos' average. */
const DEFAULT_FIT = { x: 0.5, r: 0.4, top: 0.35, bottom: 0.65, top_curve: 0.065, bottom_curve: 0.085 };

/**
 * A finished design: the photo projected back onto the cake it shows. Each point of the side takes the photo's
 * pixel for its angle round the cake and its height: the front as photographed (so seen from the front at the
 * photo's height it is the photo), easing in towards the sides (designAngle()), the back half its mirror image, so it continues without a seam; the top takes the photo's view of the top, squashed back to a circle.
 * toppings: [{ texture, count, ring, size, angle }] (see render()); cleanTop: the photo with them painted out of the top, or null.
 * turn: two or more photos round the cake (the cover first, same framing): the side is then unrolled from all of them
 * (turnTexture()), so the back and the sides are what was photographed there, not the front's mirror image.
 * Returns { group, height, foot, viewPhi } (viewPhi: the camera angle the photo was taken from).
 */
function buildDesignCake(photo, fitIn, toppings, radius, anisotropy, cleanTop = null, turn = []) {
    const fit = { ...DEFAULT_FIT, ...(fitIn ?? {}) };
    const aspect = photo.image.width / photo.image.height; // photo width per height
    // Photo heights, in photo widths: the unit radius (fit.r) is in widths too.
    const perWidth = (share) => share / aspect;
    const height = radius * perWidth(fit.bottom - fit.top) / fit.r;
    const viewPhi = Math.PI / 2 - Math.asin(Math.min(0.9, perWidth(fit.top_curve) / fit.r));

    const map = photo.clone();
    map.anisotropy = anisotropy;
    map.wrapS = map.wrapT = THREE.ClampToEdgeWrapping;
    map.needsUpdate = true;
    // The photo's own colors and light (not relit), only darkening a little as the side turns away.
    const material = photoMaterial(map, DESIGN_SHADE);

    const group = new THREE.Group();
    const add = (mesh) => {
        mesh.castShadow = true;
        mesh.receiveShadow = true;
        group.add(mesh);
        return mesh;
    };
    // A photo point (shares of width and height) as a texture coordinate (v counts up from the bottom).
    const uvAt = (x, y) => [x, 1 - y];

    // The side. Its angle round from the front (+z) is the photo's angle on the front half, mirrored on the back.
    // With a turn of photos it keeps the cylinder's own coordinates (u = once round from the front, v = up) and wears
    // the side unrolled from every photo instead.
    const side = new THREE.CylinderGeometry(radius, radius, height, SEGMENTS, 8, true);
    side.translate(0, height / 2, 0);
    let sideMaterial = material;
    if (turn.length > 1) {
        const unrolled = new THREE.CanvasTexture(turnTexture(turn, fit));
        unrolled.colorSpace = THREE.SRGBColorSpace;
        unrolled.wrapS = THREE.RepeatWrapping; // round the back the last column meets the first
        unrolled.wrapT = THREE.ClampToEdgeWrapping;
        unrolled.anisotropy = anisotropy;
        sideMaterial = photoMaterial(unrolled, DESIGN_SHADE);
    } else {
        const position = side.attributes.position;
        const uv = side.attributes.uv;
        for (let i = 0; i < position.count; i++) {
            const theta = Math.atan2(position.getX(i), position.getZ(i));
            const round = Math.abs(theta) <= Math.PI / 2 ? Math.abs(theta) : Math.PI - Math.abs(theta);
            const phi = Math.sign(theta) * designAngle(round);
            const t = position.getY(i) / height; // 0 at the bottom
            const bottom = fit.bottom - fit.bottom_curve * (1 - Math.cos(phi));
            const top = fit.top - fit.top_curve * (1 - Math.cos(phi));
            uv.setXY(i, ...uvAt(fit.x + fit.r * Math.sin(phi), bottom + (top - bottom) * t));
        }
    }
    add(new THREE.Mesh(side, sideMaterial));

    // The top: a point (x, z) on it is seen in the photo across the ellipse of the top (z towards the camera is lower).
    const top = new THREE.CircleGeometry(radius, SEGMENTS);
    top.rotateX(-Math.PI / 2);
    const topPosition = top.attributes.position;
    const topUv = top.attributes.uv;
    for (let i = 0; i < topPosition.count; i++) {
        const x = topPosition.getX(i) / radius;
        const z = topPosition.getZ(i) / radius;
        topUv.setXY(i, ...uvAt(fit.x + fit.r * x, fit.top - fit.top_curve + fit.top_curve * z));
    }
    // The top without the photo's toppings printed on it (they stand on it instead), so nothing smears as it turns:
    // the photo with them painted out when the server made one, else their colors filled away here.
    let topMap = map;
    if (cleanTop) {
        topMap = cleanTop.clone();
        topMap.wrapS = topMap.wrapT = THREE.ClampToEdgeWrapping;
        topMap.needsUpdate = true;
    } else if (toppings.length) {
        topMap = cleanTopTexture(photo, fit, toppings.map((t) => t.texture));
    }
    topMap.anisotropy = anisotropy;
    add(new THREE.Mesh(top, photoMaterial(topMap, 0))).position.y = height;
    if (sideMaterial !== material && topMap !== map) { // the photo's own copy ended up unused
        material.dispose();
        map.dispose();
    }

    // The toppings, cut out of the photo, standing on the top where the photo has them, so they go round with the
    // cake. Each always faces the camera: piped and piled toppings look much the same from every side.
    toppings.forEach(({ texture, count, ring, size, angle }) => {
        const map = texture.clone(); // the loaded picture stays for the next pick
        map.needsUpdate = true;
        const material = new THREE.SpriteMaterial({ map, alphaTest: 0.4, toneMapped: false });
        const width = size * radius;
        const tall = width * (texture.image.height / texture.image.width);
        for (let i = 0; i < count; i++) {
            const around = THREE.MathUtils.degToRad(angle) + (i / count) * Math.PI * 2;
            const sprite = new THREE.Sprite(material);
            sprite.center.set(0.5, TOPPING_SINK); // stands on the top, sunk in a little
            sprite.scale.set(width, tall, 1);
            sprite.position.set(Math.sin(around) * ring * radius, height, Math.cos(around) * ring * radius);
            group.add(sprite);
        }
    });

    // The pedestal stand from the photos: a wide plate on a slim stem and a flared foot, in champagne gold.
    const foot = addStand(group, radius);

    return { group, height, foot, viewPhi };
}

/**
 * The photo's angle to read for an angle round the cake from the front (0 to PI/2). Up to DESIGN_TRUE it is the same
 * angle; past it the photo is read ever more slowly, reaching DESIGN_EDGE at the side. It keeps moving right up to the
 * side, so no column of the photo is smeared into streaks, and the photo's foreshortened, background-edged rim is never read.
 */
function designAngle(round, limit = Math.PI / 2) {
    // A photo that only has to reach a little way round (one of a turn) is read as it is.
    if (limit <= DESIGN_EDGE) return Math.min(round, limit);
    if (round <= DESIGN_TRUE) return round;
    // Past 2 * DESIGN_EDGE - DESIGN_TRUE the ease would turn back on itself; a turn of two photos stays under it.
    const end = Math.min(limit, 2 * DESIGN_EDGE - DESIGN_TRUE - 0.01);
    const span = end - DESIGN_TRUE;
    const past = Math.min(round, end) - DESIGN_TRUE;

    return DESIGN_TRUE + past - (end - DESIGN_EDGE) * (past / span) ** 2;
}

/**
 * The side of a design unrolled from a turn of photos, as a canvas: column u is the angle u * 2PI round from the front
 * (towards +x), row 0 is the top of the side. Photo k was taken from k / n of a turn round (DESIGN_TURN says which way),
 * so each angle is read from the photo that faces it most, the same way the cover is read (designAngle() and the
 * curves of fit), and neighbouring photos cross-fade over TURN_BLEND where they meet. Every photo is evened out to the
 * cover's brightness first, so a photo taken in slightly different light doesn't show as a band.
 */
function turnTexture(photos, fit) {
    const reads = photos.map((photo) => pixelsOf(photo.image, 2048));
    const cover = reads[0];
    const n = reads.length;
    const width = Math.min(2048, Math.max(1024, Math.round((2 * Math.PI * fit.r * cover.width) / 256) * 256));
    const height = Math.min(1024, Math.max(128, Math.round((fit.bottom - fit.top) * cover.height * 1.25)));

    // Each photo's brightness gain: the middle of its side, against the cover's.
    const brightness = ({ data, width: w, height: h }) => {
        let sum = 0;
        let count = 0;
        for (let y = Math.floor(fit.top * h); y < fit.bottom * h; y += 2) {
            for (let x = Math.floor((fit.x - fit.r * 0.4) * w); x < (fit.x + fit.r * 0.4) * w; x += 2) {
                if (x < 0 || y < 0 || x >= w || y >= h) continue;
                const i = (y * w + x) * 4;
                sum += data[i] * 0.299 + data[i + 1] * 0.587 + data[i + 2] * 0.114;
                count++;
            }
        }
        return count ? sum / count : 128;
    };
    const base = brightness(cover);
    const gains = reads.map((read, k) => (k === 0 ? 1 : Math.min(1.25, Math.max(0.8, base / Math.max(1, brightness(read))))));

    // Reads a photo between its pixels (x, y in shares), so the unrolled side is as smooth as the photo.
    const sample = ({ data, width: w, height: h }, sx, sy, out, weight) => {
        const x = Math.min(w - 1.001, Math.max(0, sx * w - 0.5));
        const y = Math.min(h - 1.001, Math.max(0, sy * h - 0.5));
        const x0 = Math.floor(x);
        const y0 = Math.floor(y);
        const fx = x - x0;
        const fy = y - y0;
        const i00 = (y0 * w + x0) * 4;
        const i10 = i00 + 4;
        const i01 = i00 + w * 4;
        const i11 = i01 + 4;
        for (let c = 0; c < 3; c++) {
            const top = data[i00 + c] + (data[i10 + c] - data[i00 + c]) * fx;
            const bottom = data[i01 + c] + (data[i11 + c] - data[i01 + c]) * fx;
            out[c] += (top + (bottom - top) * fy) * weight;
        }
    };

    const half = Math.PI / n; // each photo is nearest for half the spacing either side
    const blend = Math.min(TURN_BLEND, half * 0.5);
    const limit = half + blend;
    const canvas = document.createElement('canvas');
    canvas.width = width;
    canvas.height = height;
    const ctx = canvas.getContext('2d');
    const image = ctx.createImageData(width, height);
    const pixel = [0, 0, 0];

    for (let column = 0; column < width; column++) {
        const theta = ((column + 0.5) / width) * Math.PI * 2;
        // The photos that see this angle, how much each counts, and where in each photo it is.
        const seen = [];
        for (let k = 0; k < n; k++) {
            const phi = Math.atan2(Math.sin(theta - DESIGN_TURN * k * half * 2), Math.cos(theta - DESIGN_TURN * k * half * 2));
            const away = Math.abs(phi);
            const weight = 1 - smoothstep(half - blend, half + blend, away);
            if (weight <= 0) continue;
            const read = Math.sign(phi) * designAngle(away, limit);
            const lift = 1 - Math.cos(read);
            seen.push({ k, weight, x: fit.x + fit.r * Math.sin(read), top: fit.top - fit.top_curve * lift, bottom: fit.bottom - fit.bottom_curve * lift });
        }
        const total = seen.reduce((sum, s) => sum + s.weight, 0) || 1;

        for (let row = 0; row < height; row++) {
            const t = 1 - (row + 0.5) / height; // 1 at the top of the side
            pixel[0] = pixel[1] = pixel[2] = 0;
            seen.forEach((s) => sample(reads[s.k], s.x, s.bottom + (s.top - s.bottom) * t, pixel, (s.weight / total) * gains[s.k]));
            const i = (row * width + column) * 4;
            image.data[i] = pixel[0];
            image.data[i + 1] = pixel[1];
            image.data[i + 2] = pixel[2];
            image.data[i + 3] = 255;
        }
    }
    ctx.putImageData(image, 0, 0);

    return canvas;
}

/** A picture's pixels, drawn at most maxWidth wide (big photos are read smaller, which is plenty and much quicker). */
function pixelsOf(source, maxWidth) {
    const scale = Math.min(1, maxWidth / source.width);
    const canvas = document.createElement('canvas');
    canvas.width = Math.max(1, Math.round(source.width * scale));
    canvas.height = Math.max(1, Math.round(source.height * scale));
    const ctx = canvas.getContext('2d', { willReadFrequently: true });
    ctx.drawImage(source, 0, 0, canvas.width, canvas.height);

    return ctx.getImageData(0, 0, canvas.width, canvas.height);
}

/* ---------- Shapes ---------- */

/** A pedestal cake stand (the cake sits at y = 0): { geometry, foot } where foot is the height of its base. */
function standGeometry(R) {
    const foot = -0.66 * R;
    // From the middle of the base, out round the flared foot, up the stem, out under the plate, round its lip, to the middle of the top.
    const outline = new THREE.SplineCurve([
        [0.001, foot], [0.4 * R, foot], [0.46 * R, foot + 0.02 * R], [0.42 * R, foot + 0.05 * R], [0.25 * R, foot + 0.1 * R],
        [0.1 * R, foot + 0.2 * R], [0.055 * R, -0.36 * R], [0.05 * R, -0.24 * R], [0.07 * R, -0.13 * R], [0.3 * R, -0.085 * R],
        [1.0 * R, -0.055 * R], [1.19 * R, -0.04 * R], [1.21 * R, -0.015 * R], [1.19 * R, 0.002 * R], [0.9 * R, 0], [0.001, 0],
    ].map(([x, y]) => new THREE.Vector2(x, y)));
    const geometry = new THREE.LatheGeometry(outline.getSpacedPoints(120), SEGMENTS);
    geometry.computeVertexNormals();

    return { geometry, foot };
}

/*
 * The sides are pushed in and out by one noise field that runs up the whole cake (baseY is where a piece starts),
 * so where one layer ends the next begins at exactly the same width: uneven, but without steps or gaps.
 */

/**
 * A sponge layer with a slightly uneven, open-crumbed side, darker at the top and bottom where cream presses on it,
 * with browner patches where the bake's crust shows.
 */
function spongeGeometry(r, h, baseY) {
    const geometry = new THREE.CylinderGeometry(r, r, h, SEGMENTS, 40, true);
    geometry.translate(0, h / 2, 0);
    crumble(geometry, r, h, baseY);
    wobble(geometry, r, WOBBLE.side, baseY);
    shadeEdges(geometry, h, 0.07, EDGE_SHADE);
    mottle(geometry, baseY, { scale: 4.5, tint: [0.86, 0.74, 0.6], share: CRUST, vary: 0.05 });

    return geometry;
}

/** Small pits and torn bits in the sponge's side (none at its top and bottom edge, so it meets the cream flush). */
function crumble(geometry, r, h, baseY) {
    const position = geometry.attributes.position;
    for (let i = 0; i < position.count; i++) {
        const x = position.getX(i);
        const y = position.getY(i);
        const z = position.getZ(i);
        const radius = Math.hypot(x, z);
        if (radius < 1e-6) continue;
        const angle = Math.atan2(z, x);
        const edge = smoothstep(0, 0.04, y) * smoothstep(0, 0.04, h - y);
        const pit = Math.max(0, fbm(Math.cos(angle) * 38, Math.sin(angle) * 38, (baseY + y) * 38 + 2) - 0.52);
        const scale = (radius - pit * 4 * CRUMB_PITS * r * edge) / radius;
        position.setXYZ(i, x * scale, y, z * scale);
    }
}

/**
 * Multiplies the vertex colors (set by shadeEdges) by patchy noise: patches tinted by `tint` (an [r, g, b]
 * multiplier) over `share` of their strength, and a light-and-dark variation of +/- `vary`.
 */
function mottle(geometry, baseY, { scale, tint, share, vary }) {
    const position = geometry.attributes.position;
    const color = geometry.attributes.color;
    for (let i = 0; i < position.count; i++) {
        const y = position.getY(i);
        const angle = Math.atan2(position.getZ(i), position.getX(i));
        const c = Math.cos(angle);
        const s = Math.sin(angle);
        const patch = smoothstep(0.45, 0.72, fbm(c * scale, s * scale, (baseY + y) * scale * 1.5)) * share;
        const light = 1 + (fbm(c * scale * 3, s * scale * 3, (baseY + y) * scale * 4 + 9) - 0.5) * 2 * vary;
        color.setXYZ(
            i,
            color.getX(i) * light * (1 - patch + patch * tint[0]),
            color.getY(i) * light * (1 - patch + patch * tint[1]),
            color.getZ(i) * light * (1 - patch + patch * tint[2]),
        );
    }
}

/**
 * The cream between layers: pressed out by the sponge on top, so it bulges in a soft round pillow, more in some
 * places than others, with fine knife ridges running round it. It meets the sponge at exactly the sponge's width
 * (no step), tucked into a shaded crease.
 */
function creamGeometry(r, h, baseY) {
    const points = [];
    for (let i = 0; i <= 36; i++) points.push(new THREE.Vector2(r, (i / 36) * h));
    const geometry = new THREE.LatheGeometry(points, SEGMENTS);
    squeezeCream(geometry, r, h, baseY);
    wobble(geometry, r, WOBBLE.side, baseY);
    shadeEdges(geometry, h, 0.035, EDGE_SHADE * 0.75);
    mottle(geometry, baseY, { scale: 3, tint: [1, 1, 1], share: 0, vary: 0.03 });

    return geometry;
}

/** Pushes the cream out: a pillow that is 0 at the sponge, uneven round the cake, with ridges along it. */
function squeezeCream(geometry, r, h, baseY) {
    const position = geometry.attributes.position;
    for (let i = 0; i < position.count; i++) {
        const x = position.getX(i);
        const y = position.getY(i);
        const z = position.getZ(i);
        const radius = Math.hypot(x, z);
        if (radius < 1e-6) continue;
        const angle = Math.atan2(z, x);
        const c = Math.cos(angle);
        const s = Math.sin(angle);
        const pillow = Math.pow(Math.sin(Math.PI * Math.min(1, Math.max(0, y / h))), 0.55);
        const squeezed = 0.45 + 1.1 * fbm(c * 1.7, s * 1.7, baseY * 3.1 + 5); // how much was pressed out here
        const ridge = fbm(c * 3, s * 3, (baseY + y) * 75) - 0.5; // long in the turn, short up the side
        const out = r * (CREAM_BULGE * squeezed + CREAM_RIDGE * ridge * 2) * pillow;
        const scale = (radius + out) / radius;
        position.setXYZ(i, x * scale, y, z * scale);
    }
}

/** The frosting edge: a quarter circle, so it curves down to the side. Uneven at the bottom like the cake, true at the top. */
function rimGeometry(r, topH, round, baseY) {
    const points = [];
    points.push(new THREE.Vector2(r, 0));
    for (let i = 10; i >= 0; i--) {
        const a = (i / 10) * (Math.PI / 2);
        points.push(new THREE.Vector2(r - round + Math.sin(a) * round, topH - round + Math.cos(a) * round));
    }
    const geometry = new THREE.LatheGeometry(points, SEGMENTS);
    wobble(geometry, r, WOBBLE.side, baseY, (y) => 1 - smoothstep(0, topH - round, y));
    shadeEdges(geometry, topH, 0.03, EDGE_SHADE * 0.4, { top: false });

    return geometry;
}

/** The top of the cake: a disc with gentle hills and the spiral marks of a spatula, flat where it meets the edge. */
function topGeometry(r, cakeRadius) {
    const geometry = new THREE.RingGeometry(0, r, SEGMENTS, 48);
    geometry.rotateX(-Math.PI / 2);
    const position = geometry.attributes.position;
    for (let i = 0; i < position.count; i++) position.setY(i, topHeight(position.getX(i), position.getZ(i), r, cakeRadius));
    geometry.computeVertexNormals();

    return geometry;
}

/** How high the top's surface is at (x, z): gentle hills and spatula strokes, flat where it meets the edge. */
function topHeight(x, z, r, cakeRadius) {
    const amount = cakeRadius * WOBBLE.top;
    const d = Math.hypot(x, z) / r;
    const angle = Math.atan2(z, x);
    const fade = 1 - smoothstep(0.82, 1, d); // meets the rim exactly
    const hills = (fbm(x * 0.9, z * 0.9, 12.5) - 0.5) * 2 * amount;
    // Faint palette-knife strokes: a loose spiral, broken up by noise so it never looks like rings.
    const strokes = Math.sin(d * 14 - angle * 1.5 + fbm(x * 2, z * 2, 3.1) * 6) * amount * 0.22 * smoothstep(0.1, 0.4, d);

    return (hills + strokes) * fade;
}

/**
 * The message piped on the top, inside the ring of rosettes: script lettering in the icing color on a thin sheet
 * that follows the top's hills, facing the camera's starting side. A blurred copy of the letters bumps them up,
 * and a soft shadow under them makes even white icing stand out on cream.
 */
function makeWriting(text, color, radius, topY, anisotropy) {
    const width = radius * WRITING.width;
    const height = radius * WRITING.height;
    const { picture, relief } = writingCanvases(text, color, width / height);

    const map = new THREE.CanvasTexture(picture);
    map.colorSpace = THREE.SRGBColorSpace;
    map.anisotropy = anisotropy;
    const bumpMap = new THREE.CanvasTexture(relief);

    // Read from where the camera starts (setFromSphericalCoords' theta of PI / 5): the letters' foot towards it.
    const geometry = new THREE.PlaneGeometry(width, height, 48, 34);
    geometry.rotateX(-Math.PI / 2);
    geometry.rotateY(Math.PI / 5);
    const position = geometry.attributes.position;
    const topR = radius - TOP_ROUND;
    for (let i = 0; i < position.count; i++) {
        position.setY(i, topHeight(position.getX(i), position.getZ(i), topR, radius) + radius * 0.003);
    }
    geometry.computeVertexNormals();

    const mesh = new THREE.Mesh(geometry, new THREE.MeshStandardMaterial({
        map, bumpMap, bumpScale: WRITING.relief, roughness: 1, transparent: true, alphaTest: 0.02, depthWrite: false,
        polygonOffset: true, polygonOffsetFactor: -4, emissive: 0xffffff, emissiveMap: map, emissiveIntensity: GLOW.cream,
    }));
    mesh.position.y = topY;
    mesh.receiveShadow = true;
    mesh.renderOrder = 1;

    return mesh;
}

/** The writing's two canvases: the colored lettering with its shadow, and a white-on-black height map of it. */
function writingCanvases(text, color, aspect) {
    const W = 1024;
    const H = Math.round(W / aspect);
    const font = (size) => `700 ${size}px ${WRITING.font}`;
    const measure = document.createElement('canvas').getContext('2d');

    // The biggest letters at which the words wrap into lines that fit; a very long message is cut short with "...".
    const wrap = (size) => {
        measure.font = font(size);
        const lines = [];
        text.split(/\s+/).forEach((word) => {
            const last = lines[lines.length - 1];
            if (last !== undefined && measure.measureText(`${last} ${word}`).width <= W * 0.92) lines[lines.length - 1] = `${last} ${word}`;
            else lines.push(word);
        });
        return lines;
    };
    let size = WRITING.maxSize;
    let lines = wrap(size);
    while (size > 56 && (lines.length * size * 1.08 > H * 0.92 || lines.some((line) => measure.measureText(line).width > W * 0.92))) {
        size -= 6;
        lines = wrap(size);
    }
    const fits = Math.max(1, Math.floor((H * 0.92) / (size * 1.08)));
    if (lines.length > fits) {
        lines = lines.slice(0, fits);
        let last = lines[fits - 1];
        while (last.length > 1 && measure.measureText(`${last}...`).width > W * 0.92) last = last.slice(0, -1);
        lines[fits - 1] = `${last.trimEnd()}...`;
    }
    lines = lines.map((line) => {
        let cut = line;
        while (cut.length > 1 && measure.measureText(cut).width > W * 0.96) cut = cut.slice(0, -1);
        return cut;
    });

    // Stroking round the letters too makes the thin script strokes thicker, like a full line of piped icing.
    const draw = (ctx, stroke = 0) => {
        ctx.font = font(size);
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.lineJoin = 'round';
        ctx.lineCap = 'round';
        ctx.lineWidth = stroke;
        ctx.strokeStyle = ctx.fillStyle;
        const first = H / 2 - ((lines.length - 1) * size * 1.08) / 2;
        lines.forEach((line, i) => {
            if (stroke > 0) ctx.strokeText(line, W / 2, first + i * size * 1.08);
            ctx.fillText(line, W / 2, first + i * size * 1.08);
        });
    };
    const thick = size * WRITING.thick;
    const canvas = () => {
        const element = document.createElement('canvas');
        element.width = W;
        element.height = H;
        return element;
    };

    const picture = canvas();
    const ctx = picture.getContext('2d');
    // A small soft contact shadow, as a line of icing casts on the cream right under it.
    ctx.shadowColor = 'rgba(45, 28, 12, 0.3)';
    ctx.shadowBlur = size * 0.035;
    ctx.shadowOffsetY = size * 0.02;
    ctx.fillStyle = color;
    draw(ctx, thick);
    ctx.shadowColor = 'transparent';
    // No painted light or shade on the letters, so the icing keeps its true color; the height map gives the 3D look.

    // The height map: a well-blurred copy, so each stroke bulges round like piped icing rather than a flat cut-out.
    const relief = canvas();
    const rctx = relief.getContext('2d');
    rctx.fillStyle = '#000';
    rctx.fillRect(0, 0, W, H);
    rctx.filter = `blur(${Math.max(3, size * 0.045)}px)`;
    rctx.fillStyle = '#fff';
    draw(rctx, thick);

    return { picture, relief };
}

/** Frees the writing's sheet, material and both its pictures. */
function disposeWriting(mesh) {
    mesh.geometry.dispose();
    mesh.material.map?.dispose();
    mesh.material.bumpMap?.dispose();
    mesh.material.dispose();
}

/** Piped rosettes around the top edge: star-tip ridges that twist up to a soft peak, each a little different. */
function makeRosettes(radius, topY, color) {
    const size = 0.2 * (radius / 2);
    const geometry = rosetteGeometry(size);
    const count = Math.round(5 * radius); // 10 on a 2 kg cake
    const material = new THREE.MeshStandardMaterial({ color: 0xffffff, roughness: 1, emissive: color, emissiveIntensity: GLOW.cream });
    const mesh = new THREE.InstancedMesh(geometry, material, count);
    const tone = new THREE.Color(color);
    const shade = new THREE.Color();
    mesh.castShadow = true;
    mesh.receiveShadow = true;

    const matrix = new THREE.Matrix4();
    const quaternion = new THREE.Quaternion();
    for (let i = 0; i < count; i++) {
        const jitter = hash(i, 3, 9) - 0.5;
        const angle = ((i + jitter * 0.25) / count) * Math.PI * 2;
        const scale = 0.9 + hash(i, 7, 1) * 0.2;
        quaternion.setFromAxisAngle(new THREE.Vector3(0, 1, 0), hash(i, 5, 2) * Math.PI * 2);
        matrix.compose(
            new THREE.Vector3(Math.cos(angle) * radius * 0.78, topY - size * 0.1, Math.sin(angle) * radius * 0.78),
            quaternion,
            new THREE.Vector3(scale, scale * (0.92 + hash(i, 1, 4) * 0.16), scale),
        );
        mesh.setMatrixAt(i, matrix);
        mesh.setColorAt(i, shade.copy(tone).multiplyScalar(0.95 + hash(i, 2, 8) * 0.07)); // piped by hand: not all alike
    }

    return mesh;
}

/** Loose crumbs on the plate, just outside the cake's foot: small rough lumps, each turned and sized differently. */
function makeCrumbs(radius, color) {
    const count = Math.round(CRUMBS * radius * 7);
    const material = new THREE.MeshStandardMaterial({ color, roughness: 1, emissive: color, emissiveIntensity: GLOW.sponge, flatShading: true });
    const mesh = new THREE.InstancedMesh(new THREE.DodecahedronGeometry(1, 0), material, count);
    mesh.castShadow = true;
    mesh.receiveShadow = true;

    const matrix = new THREE.Matrix4();
    const quaternion = new THREE.Quaternion();
    const euler = new THREE.Euler();
    for (let i = 0; i < count; i++) {
        // Most land close to the cake, a few roll further out.
        const angle = hash(i, 11, 4) * Math.PI * 2;
        const out = radius * (1.03 + Math.pow(hash(i, 6, 13), 2) * 0.12);
        const size = radius * (0.006 + Math.pow(hash(i, 9, 2), 3) * 0.014);
        quaternion.setFromEuler(euler.set(hash(i, 1, 1) * 6.3, hash(i, 2, 2) * 6.3, hash(i, 3, 3) * 6.3));
        matrix.compose(
            new THREE.Vector3(Math.cos(angle) * out, size * 0.5, Math.sin(angle) * out),
            quaternion,
            new THREE.Vector3(size, size * (0.6 + hash(i, 4, 4) * 0.4), size * (0.7 + hash(i, 5, 5) * 0.5)),
        );
        mesh.setMatrixAt(i, matrix);
    }

    return mesh;
}

function rosetteGeometry(size) {
    const radial = 96;
    const rings = 36;
    const ridges = 8;
    const positions = [];
    const uvs = [];
    const indices = [];

    for (let j = 0; j <= rings; j++) {
        const v = j / rings;
        // Wide and rounded at the bottom, tapering to a peak that leans over a little.
        const profile = size * (1 - Math.pow(v, 1.7)) * (0.82 + 0.18 * Math.sin(Math.min(v / 0.3, 1) * (Math.PI / 2)));
        const lean = size * 0.22 * Math.pow(v, 3);
        for (let i = 0; i <= radial; i++) {
            const u = i / radial;
            const angle = u * Math.PI * 2;
            const ridge = 1 + 0.2 * Math.cos(ridges * angle + v * 3.2) * (1 - v * 0.5);
            const r = profile * ridge;
            positions.push(Math.cos(angle) * r + lean, v * size * 1.35, Math.sin(angle) * r);
            uvs.push(u, v);
        }
    }
    for (let j = 0; j < rings; j++) {
        for (let i = 0; i < radial; i++) {
            const a = j * (radial + 1) + i;
            const b = a + radial + 1;
            indices.push(a, b, a + 1, b, b + 1, a + 1);
        }
    }

    const geometry = new THREE.BufferGeometry();
    geometry.setAttribute('position', new THREE.Float32BufferAttribute(positions, 3));
    geometry.setAttribute('uv', new THREE.Float32BufferAttribute(uvs, 2));
    geometry.setIndex(indices);
    geometry.computeVertexNormals();
    smoothSeam(geometry);

    return geometry;
}

/** Pushes every point in or out a little (smooth noise around and up the cake), then fixes the normals. */
function wobble(geometry, radius, share, baseY, weight = () => 1) {
    const position = geometry.attributes.position;
    for (let i = 0; i < position.count; i++) {
        const x = position.getX(i);
        const y = position.getY(i);
        const z = position.getZ(i);
        const r = Math.hypot(x, z);
        if (r < 1e-6) continue;
        const angle = Math.atan2(z, x);
        // Noise sampled on a circle, so it closes up where the shape wraps around.
        const big = fbm(Math.cos(angle) * 2.2, Math.sin(angle) * 2.2, (baseY + y) * 2.5) - 0.5;
        const fine = fbm(Math.cos(angle) * 14, Math.sin(angle) * 14, (baseY + y) * 18) - 0.5;
        const scale = (r + (big * 2 + fine * 0.6) * share * radius * weight(y)) / r;
        position.setXYZ(i, x * scale, y, z * scale);
    }
    geometry.computeVertexNormals();
    smoothSeam(geometry);
}

/** Vertex colors that darken the side near its top and bottom edges: the shade where one layer presses on the next. */
function shadeEdges(geometry, height, falloff, strength, { top = true, bottom = true } = {}) {
    const position = geometry.attributes.position;
    const colors = new Float32Array(position.count * 3);
    for (let i = 0; i < position.count; i++) {
        const y = position.getY(i);
        const distance = Math.min(bottom ? y : Infinity, top ? height - y : Infinity);
        const shade = 1 - strength * (1 - smoothstep(0, falloff, distance));
        colors.set([shade, shade, shade], i * 3);
    }
    geometry.setAttribute('color', new THREE.BufferAttribute(colors, 3));
}

/** Points that sit in the same place (where a shape wraps around) share one normal, so no line shows there. */
function smoothSeam(geometry) {
    const position = geometry.attributes.position;
    const normal = geometry.attributes.normal;
    const groups = new Map();
    for (let i = 0; i < position.count; i++) {
        const key = `${position.getX(i).toFixed(4)},${position.getY(i).toFixed(4)},${position.getZ(i).toFixed(4)}`;
        if (!groups.has(key)) groups.set(key, []);
        groups.get(key).push(i);
    }
    const sum = new THREE.Vector3();
    groups.forEach((indices) => {
        if (indices.length < 2) return;
        sum.set(0, 0, 0);
        indices.forEach((i) => sum.add(new THREE.Vector3().fromBufferAttribute(normal, i)));
        sum.normalize();
        indices.forEach((i) => normal.setXYZ(i, sum.x, sum.y, sum.z));
    });
}

/* ---------- Materials ---------- */

/**
 * The side of a layer. A strip is wrapped once around; a tileable picture repeats a whole number of times
 * around (so it never meets itself at a seam), as big as the grain size says. Sponge and cream are both matte
 * (no shine) and let a little light through (GLOW), so their shadows stay soft like real food.
 */
function sideMaterial(kind, surface, picture, height, circumference, anisotropy, offset = 0) {
    const base = picture ?? patternTexture(kind, surface?.color || FALLBACK_COLOR[kind]);
    const map = base.clone();
    map.wrapS = map.wrapT = THREE.RepeatWrapping;
    map.anisotropy = anisotropy;

    const image = base.image;
    if (picture && image && image.width / image.height > STRIP_RATIO) {
        map.repeat.set(1, 1);
    } else {
        const tile = TILE_WORLD * (Number(surface?.scale) || 1);
        map.repeat.set(Math.max(1, Math.round(circumference / tile)), height / tile);
    }
    map.offset.x = offset;
    map.needsUpdate = true;

    if (kind === 'sponge') {
        return new THREE.MeshStandardMaterial({
            map, bumpMap: map, bumpScale: BUMP.sponge, roughness: 1, vertexColors: true,
            emissive: 0xffffff, emissiveMap: map, emissiveIntensity: GLOW.sponge,
        });
    }

    return new THREE.MeshStandardMaterial({
        map, bumpMap: map, bumpScale: BUMP.cream, roughness: 1, vertexColors: true, side: THREE.DoubleSide,
        emissive: 0xffffff, emissiveMap: map, emissiveIntensity: GLOW.cream,
    });
}

/** The top of the cake: the cream's top picture once, else its side picture repeated across the disc. */
function topMaterial(cream, loaded, radius, anisotropy) {
    const top = loaded.get(cream?.top);
    const base = top ?? loaded.get(cream?.side) ?? patternTexture('cream', cream?.color || FALLBACK_COLOR.cream);
    const map = base.clone();
    map.anisotropy = anisotropy;

    if (top) {
        map.wrapS = map.wrapT = THREE.ClampToEdgeWrapping;
    } else {
        const tile = TILE_WORLD * (Number(cream?.scale) || 1);
        map.wrapS = map.wrapT = THREE.RepeatWrapping;
        map.repeat.set((2 * radius) / tile, (2 * radius) / tile);
    }
    map.needsUpdate = true;

    return new THREE.MeshStandardMaterial({
        map, bumpMap: map, bumpScale: BUMP.cream, roughness: 1, emissive: 0xffffff, emissiveMap: map, emissiveIntensity: GLOW.cream,
    });
}

/**
 * A design photo with the toppings taken off its top: inside the top's ellipse, pixels of a color the topping
 * cut-outs are made of (and that is no more common on the top than in the cut-outs, so the top's own colors stay)
 * are filled from the nearest kept pixel along their row.
 */
function cleanTopTexture(photo, fit, cutouts) {
    const bin = (r, g, b) => ((r >> 4) << 8) | ((g >> 4) << 4) | (b >> 4);
    const read = (image) => {
        const canvas = document.createElement('canvas');
        canvas.width = image.width;
        canvas.height = image.height;
        const ctx = canvas.getContext('2d', { willReadFrequently: true });
        ctx.drawImage(image, 0, 0);
        return { canvas, ctx, pixels: ctx.getImageData(0, 0, image.width, image.height) };
    };

    // How common each color is in the cut-outs.
    const cutoutShare = new Float32Array(4096);
    let cutoutTotal = 0;
    cutouts.forEach((texture) => {
        const { data } = read(texture.image).pixels;
        for (let i = 0; i < data.length; i += 4) {
            if (data[i + 3] < 128) continue;
            cutoutShare[bin(data[i], data[i + 1], data[i + 2])]++;
            cutoutTotal++;
        }
    });
    if (!cutoutTotal) return photo;

    const { canvas, ctx, pixels } = read(photo.image);
    const { width, height, data } = pixels;
    const cx = fit.x * width;
    const r = fit.r * width;
    const centerY = (fit.top - fit.top_curve) * height;
    const b = fit.top_curve * height;
    const inside = (x, y) => ((x - cx) / r) ** 2 + ((y - centerY) / b) ** 2 <= 1;

    // How common each color is on the top.
    const topShare = new Float32Array(4096);
    let topTotal = 0;
    for (let y = Math.floor(centerY - b); y <= centerY + b; y++) {
        for (let x = Math.floor(cx - r); x <= cx + r; x++) {
            if (!inside(x, y)) continue;
            const i = (y * width + x) * 4;
            topShare[bin(data[i], data[i + 1], data[i + 2])]++;
            topTotal++;
        }
    }
    const topping = (i) => {
        const k = bin(data[i], data[i + 1], data[i + 2]);
        return cutoutShare[k] / cutoutTotal > 0.001 && topShare[k] / topTotal <= cutoutShare[k] / cutoutTotal;
    };

    for (let y = Math.max(0, Math.floor(centerY - b)); y <= Math.min(height - 1, centerY + b); y++) {
        const row = [];
        for (let x = Math.max(0, Math.floor(cx - r)); x <= Math.min(width - 1, cx + r); x++) {
            if (inside(x, y)) row.push({ x, gone: topping((y * width + x) * 4) });
        }
        const kept = row.filter((p) => !p.gone);
        if (!kept.length) continue;
        row.filter((p) => p.gone).forEach((p) => {
            const nearest = kept.reduce((best, q) => (Math.abs(q.x - p.x) < Math.abs(best.x - p.x) ? q : best));
            const from = (y * width + nearest.x) * 4;
            const to = (y * width + p.x) * 4;
            data[to] = data[from];
            data[to + 1] = data[from + 1];
            data[to + 2] = data[from + 2];
        });
    }
    ctx.putImageData(pixels, 0, 0);

    const texture = new THREE.CanvasTexture(canvas);
    texture.colorSpace = THREE.SRGBColorSpace;
    return texture;
}

/** A design photo shown as it is (no tone mapping, no relighting), darkened by `shade` where the surface turns away. */
function photoMaterial(map, shade) {
    return new THREE.ShaderMaterial({
        uniforms: { map: { value: map }, shade: { value: shade } },
        vertexShader: `
            varying vec2 vUv;
            varying vec3 vNormal;
            varying vec3 vView;
            void main() {
                vUv = uv;
                vec4 position = modelViewMatrix * vec4(position, 1.0);
                vNormal = normalize(normalMatrix * normal);
                vView = normalize(-position.xyz);
                gl_Position = projectionMatrix * position;
            }`,
        fragmentShader: `
            uniform sampler2D map;
            uniform float shade;
            varying vec2 vUv;
            varying vec3 vNormal;
            varying vec3 vView;
            void main() {
                float facing = pow(max(dot(normalize(vNormal), normalize(vView)), 0.0), 0.5);
                gl_FragColor = vec4(texture2D(map, vUv).rgb * (1.0 - shade * (1.0 - facing)), 1.0);
                #include <colorspace_fragment>
            }`,
        toneMapped: false,
    });
}

const pictures = new Map();
function loadTexture(url) {
    if (!pictures.has(url)) {
        pictures.set(url, new THREE.TextureLoader().loadAsync(url)
            .then((texture) => {
                texture.colorSpace = THREE.SRGBColorSpace;
                return texture;
            })
            .catch(() => null)); // a missing picture falls back to the color pattern
    }

    return pictures.get(url);
}

/** The average color of a picture (for the rosettes, so they match the cream), else the fallback color. */
function averageColor(texture, fallback) {
    const image = texture?.image;
    if (!image) return fallback;
    try {
        const canvas = document.createElement('canvas');
        canvas.width = canvas.height = 1;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(image, 0, 0, 1, 1);
        const [r, g, b] = ctx.getImageData(0, 0, 1, 1).data;
        return `rgb(${r}, ${g}, ${b})`;
    } catch (e) {
        return fallback;
    }
}

/** A tileable crumb (sponge) or soft cream pattern in the flavor's color, drawn once per color. */
const patterns = new Map();
function patternTexture(kind, color) {
    const key = `${kind}:${color}`;
    if (patterns.has(key)) return patterns.get(key);

    const size = 512;
    const canvas = document.createElement('canvas');
    canvas.width = canvas.height = size;
    const ctx = canvas.getContext('2d');
    ctx.fillStyle = color;
    ctx.fillRect(0, 0, size, size);

    let seed = [...key].reduce((h, ch) => (h * 31 + ch.charCodeAt(0)) >>> 0, 7);
    const random = () => {
        seed = (seed + 0x6d2b79f5) >>> 0;
        let t = seed;
        t = Math.imul(t ^ (t >>> 15), t | 1);
        t ^= t + Math.imul(t ^ (t >>> 7), t | 61);
        return ((t ^ (t >>> 14)) >>> 0) / 4294967296;
    };
    // Draws a shape at (x, y) and again across each edge it touches, so the pattern tiles.
    const wrapped = (x, y, reach, draw) => {
        for (const dx of [-size, 0, size]) {
            for (const dy of [-size, 0, size]) {
                if (x + dx > -reach && x + dx < size + reach && y + dy > -reach && y + dy < size + reach) draw(x + dx, y + dy);
            }
        }
    };

    if (kind === 'sponge') {
        // Many small, irregular air holes with darker insides and lighter crumbs between them.
        for (let i = 0; i < 6000; i++) {
            const x = random() * size;
            const y = random() * size;
            const r = 0.5 + Math.pow(random(), 3) * 4;
            const hole = random() < 0.6;
            ctx.fillStyle = hole ? `rgba(60, 35, 12, ${0.12 + random() * 0.25})` : `rgba(255, 248, 235, ${0.06 + random() * 0.14})`;
            wrapped(x, y, r, (px, py) => {
                ctx.beginPath();
                ctx.ellipse(px, py, r, r * (0.5 + random() * 0.5), random() * Math.PI, 0, Math.PI * 2);
                ctx.fill();
            });
        }
    } else {
        // Soft, wide streaks, like cream smoothed with a palette knife, then finer drag lines along them.
        for (let i = 0; i < 160; i++) {
            const x = random() * size;
            const y = random() * size;
            const w = 40 + random() * 120;
            const h = 1.5 + random() * 5;
            ctx.fillStyle = random() < 0.5 ? `rgba(255, 255, 255, ${0.04 + random() * 0.08})` : `rgba(120, 80, 40, ${0.02 + random() * 0.04})`;
            wrapped(x, y, w, (px, py) => {
                ctx.beginPath();
                ctx.ellipse(px, py, w, h, (random() - 0.5) * 0.2, 0, Math.PI * 2);
                ctx.fill();
            });
        }
        for (let i = 0; i < 260; i++) {
            const x = random() * size;
            const y = random() * size;
            const w = 20 + random() * 70;
            ctx.fillStyle = random() < 0.5 ? `rgba(255, 255, 255, ${0.05 + random() * 0.07})` : `rgba(110, 70, 35, ${0.03 + random() * 0.05})`;
            wrapped(x, y, w, (px, py) => {
                ctx.beginPath();
                ctx.ellipse(px, py, w, 0.5 + random() * 0.8, (random() - 0.5) * 0.08, 0, Math.PI * 2);
                ctx.fill();
            });
        }
        // Tiny air bubbles whipped into the cream: a shaded pit with a light lower edge.
        for (let i = 0; i < 420; i++) {
            const x = random() * size;
            const y = random() * size;
            const r = 0.6 + Math.pow(random(), 2.5) * 2.4;
            wrapped(x, y, r + 1, (px, py) => {
                ctx.fillStyle = `rgba(255, 255, 255, ${0.12 + random() * 0.12})`;
                ctx.beginPath();
                ctx.arc(px, py + r * 0.35, r, 0, Math.PI * 2);
                ctx.fill();
                ctx.fillStyle = `rgba(90, 60, 30, ${0.1 + random() * 0.14})`;
                ctx.beginPath();
                ctx.arc(px, py, r * 0.8, 0, Math.PI * 2);
                ctx.fill();
            });
        }
    }

    const texture = new THREE.CanvasTexture(canvas);
    texture.colorSpace = THREE.SRGBColorSpace;
    patterns.set(key, texture);

    return texture;
}

let shadowMap = null;
function shadowTexture() {
    if (shadowMap) return shadowMap;
    const canvas = document.createElement('canvas');
    canvas.width = canvas.height = 128;
    const ctx = canvas.getContext('2d');
    const gradient = ctx.createRadialGradient(64, 64, 30, 64, 64, 64);
    gradient.addColorStop(0, 'rgba(70, 50, 30, 0.4)');
    gradient.addColorStop(1, 'rgba(70, 50, 30, 0)');
    ctx.fillStyle = gradient;
    ctx.fillRect(0, 0, 128, 128);
    shadowMap = new THREE.CanvasTexture(canvas);

    return shadowMap;
}

/** A dark band fading out just past the cake's foot (the cake covers the middle): 1 / 1.2 of the way out is its edge. */
let occlusionMap = null;
function occlusionTexture() {
    if (occlusionMap) return occlusionMap;
    const canvas = document.createElement('canvas');
    canvas.width = canvas.height = 256;
    const ctx = canvas.getContext('2d');
    const gradient = ctx.createRadialGradient(128, 128, 0, 128, 128, 128);
    gradient.addColorStop(0, `rgba(55, 38, 20, ${CONTACT_SHADE})`);
    gradient.addColorStop(0.82, `rgba(55, 38, 20, ${CONTACT_SHADE})`);
    gradient.addColorStop(0.88, `rgba(55, 38, 20, ${CONTACT_SHADE * 0.35})`);
    gradient.addColorStop(1, 'rgba(55, 38, 20, 0)');
    ctx.fillStyle = gradient;
    ctx.fillRect(0, 0, 256, 256);
    occlusionMap = new THREE.CanvasTexture(canvas);
    occlusionMap.colorSpace = THREE.SRGBColorSpace;

    return occlusionMap;
}

/** Frees what one cake used; the shared pictures and patterns stay for the next pick. */
function disposeGroup(group) {
    group.traverse((object) => {
        object.geometry?.dispose();
        const materials = Array.isArray(object.material) ? object.material : [object.material];
        materials.filter(Boolean).forEach((material) => {
            if (material.map && material.map !== shadowMap && material.map !== occlusionMap) material.map.dispose();
            material.uniforms?.map?.value?.dispose();
            material.dispose();
        });
    });
}

/* ---------- Noise ---------- */

function hash(x, y, z) {
    let h = (Math.imul(x, 374761393) + Math.imul(y, 668265263) + Math.imul(z, 1440662683)) | 0;
    h = Math.imul(h ^ (h >>> 13), 1274126177);
    return ((h ^ (h >>> 16)) >>> 0) / 4294967295;
}

function smoothstep(a, b, x) {
    const t = Math.min(1, Math.max(0, (x - a) / (b - a)));
    return t * t * (3 - 2 * t);
}

/** Smooth value noise in 3D, from 0 to 1. */
function noise(x, y, z) {
    const xi = Math.floor(x);
    const yi = Math.floor(y);
    const zi = Math.floor(z);
    const [fx, fy, fz] = [x - xi, y - yi, z - zi].map((f) => f * f * (3 - 2 * f));
    const lerp = (a, b, t) => a + (b - a) * t;
    const corner = (dx, dy, dz) => hash(xi + dx, yi + dy, zi + dz);

    return lerp(
        lerp(lerp(corner(0, 0, 0), corner(1, 0, 0), fx), lerp(corner(0, 1, 0), corner(1, 1, 0), fx), fy),
        lerp(lerp(corner(0, 0, 1), corner(1, 0, 1), fx), lerp(corner(0, 1, 1), corner(1, 1, 1), fx), fy),
        fz,
    );
}

/** Three octaves of noise: big gentle shapes with finer detail on top, from 0 to 1. */
function fbm(x, y, z) {
    return (noise(x, y, z) * 0.57 + noise(x * 2.03, y * 2.03, z * 2.03) * 0.29 + noise(x * 4.1, y * 4.1, z * 4.1) * 0.14);
}
