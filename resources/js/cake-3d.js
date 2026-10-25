// The 3D cake, for the design page and the Cake images page. Only this small file loads with the page:
// three.js and the viewer download the first time someone opens a 3D view.
window.CakeViewer3D = {
    async create(container) {
        const { createCakeViewer } = await import('./cake-3d/viewer.js');

        return createCakeViewer(container);
    },
};

window.dispatchEvent(new Event('cake-3d:ready'));
