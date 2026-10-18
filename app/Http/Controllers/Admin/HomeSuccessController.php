<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeSuccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class HomeSuccessController extends Controller
{
    /** Only authenticated site administrators can manage homepage success content. */
    private function authorizeAdmin(): void
    {
        $user = Auth::user();
        abort_unless($user !== null, 403);

        // The existing site's sidebar uses authorizeRoles([...]), not a single role string.
        // Match the existing authorization mechanism and support both administrator roles.
        $allowed = false;
        if (method_exists($user, 'authorizeRoles')) {
            try {
                $allowed = (bool) $user->authorizeRoles(['SuperAdmin', 'Admin']);
            } catch (\TypeError $e) {
                // Defensive compatibility with implementations that take a scalar role.
                $allowed = (bool) $user->authorizeRoles('SuperAdmin')
                    || (bool) $user->authorizeRoles('Admin');
            }
        }
        abort_unless($allowed, 403);
    }

    public function index()
    {
        $this->authorizeAdmin();
        $items = HomeSuccess::orderBy('type')->orderBy('sort_order')->orderBy('id')->get();
        return view('admin.home-success.index', compact('items'));
    }

    public function create()
    {
        $this->authorizeAdmin();
        $item = new HomeSuccess(['type' => 'info', 'is_active' => true, 'sort_order' => 0]);
        return view('admin.home-success.form', compact('item'));
    }

    public function edit(HomeSuccess $success)
    {
        $this->authorizeAdmin();
        $item = $success;
        return view('admin.home-success.form', compact('item'));
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'type' => 'required|in:info,photo,feature',
            'title' => 'required|string|max:180',
            'description' => 'nullable|string|max:2500',
            'subtitle' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:100|regex:/^[a-zA-Z0-9 -]+$/',
            'image' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:4096',
            'sort_order' => 'nullable|integer|min:0|max:99999',
            'is_active' => 'nullable|boolean',
        ]);
    }

    private function handleImage(Request $request, array &$data, ?HomeSuccess $old = null): void
    {
        unset($data['image']);
        if (!$request->hasFile('image')) {
            return;
        }
        $file = $request->file('image');
        $directory = public_path('web/uploads/home-success');
        if (!File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }
        $name = (string) Str::uuid() . '.' . $file->extension();
        $file->move($directory, $name);
        $data['image_path'] = 'web/uploads/home-success/' . $name;
        if ($old && $old->image_path) {
            $this->removeStoredPhoto($old->image_path);
        }
    }

    private function removeStoredPhoto(?string $path): void
    {
        if (!$path || !str_starts_with($path, 'web/uploads/home-success/')) {
            return;
        }
        $file = public_path($path);
        if (File::isFile($file)) {
            File::delete($file);
        }
    }

    public function store(Request $request)
    {
        $this->authorizeAdmin();
        $data = $this->validated($request);
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $this->handleImage($request, $data);
        HomeSuccess::create($data);
        return redirect()->route('admin.home-success.index')->with('success', 'Success item created.');
    }

    public function update(Request $request, HomeSuccess $success)
    {
        $this->authorizeAdmin();
        $data = $this->validated($request);
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $this->handleImage($request, $data, $success);
        $success->update($data);
        return redirect()->route('admin.home-success.index')->with('success', 'Success item updated.');
    }

    public function deletePhoto(HomeSuccess $success)
    {
        $this->authorizeAdmin();
        $this->removeStoredPhoto($success->image_path);
        $success->update(['image_path' => null]);
        return back()->with('success', 'Photo deleted.');
    }

    public function destroy(HomeSuccess $success)
    {
        $this->authorizeAdmin();
        $this->removeStoredPhoto($success->image_path);
        $success->delete();
        return back()->with('success', 'Success item deleted.');
    }
}
