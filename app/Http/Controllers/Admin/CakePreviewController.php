<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CakePreview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CakePreviewController extends Controller
{
    public function index(): View
    {
        $previews = CakePreview::with(['spongeFlavor', 'creamFlavor'])->get();

        return view('admin.cake-previews.index', [
            'previews' => $previews,
        ]);
    }

    public function update(Request $request, CakePreview $cakePreview): RedirectResponse
    {

        $validated = $request->validate([
            'image' => ['required', 'image', 'max:10240'], // 10MB max
        ]);

        if ($cakePreview->image) {
            Storage::disk('public')->delete($cakePreview->image);
        }

        $path = $request->file('image')->store('cake-previews', 'public');

        $cakePreview->update(['image' => $path]);

        return redirect()
            ->route('admin.cake-previews.index')
            ->with('success', "Updated preview for {$cakePreview->spongeFlavor->name} + {$cakePreview->creamFlavor->name}");
    }
}
