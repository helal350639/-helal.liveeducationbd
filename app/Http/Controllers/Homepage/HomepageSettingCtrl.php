<?php

namespace App\Http\Controllers\Homepage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Filter;
use App\Models\SubFilter;
use App\Models\Role;
use App\Models\AnswerFile;
use App\Models\Notice;
use Auth;
use Image;
use Toastr;
use File;
use Session;
use Validator;

class HomepageSettingCtrl extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
  }

  public function aboutUs()
  {
    if(!Storage::exists('about.json'))
    {
      Storage::put('about.json', json_encode([]));
    }

    $json = Storage::get('about.json');
    $array = json_decode($json, true);
    $about = (object) $array;
    
    return view('layouts.settings.about-us', compact('about'));
  }

  public function aboutUsUpdate(Request $request)
  {
    $data = $request->all();
    if(isset($data['_token'])){
      unset($data['_token']);
    }

    $parts = parse_url($data['video']);
    parse_str($parts['query'], $queryParams);
    $data['video'] = $queryParams['v'] ?? null;

    $result = Storage::put('about.json', json_encode($data, JSON_PRETTY_PRINT));

    return redirect()->route('settings.about-us');
  }

  public function noticeEdit()
  {
    Notice::purgeExpiredImages();
    $notices = Notice::latest('id')->paginate(15);
    return view('layouts.homepagesettings.notice-index', compact('notices'));
  }

  public function noticeCreate()
  {
    $notice = new Notice();
    return view('layouts.homepagesettings.notice-form', compact('notice'));
  }

  public function noticeEditById($id)
  {
    $notice = Notice::findOrFail($id);
    return view('layouts.homepagesettings.notice-form', compact('notice'));
  }

  public function noticeStore(Request $request)
  {
    $data = $this->validateNotice($request);
    if (($data['notice_type'] ?? '') === 'image' && !$request->hasFile('image')) {
      throw \Illuminate\Validation\ValidationException::withMessages(['image' => 'Image is required for Image Only notice.']);
    }
    $data['image'] = $this->storeNoticeImage($request);
    Notice::create($data);
    Session::flash('success', 'Notice successfully added.');
    return redirect()->route('noitice.edit');
  }

  public function noticeUpdate(Request $request, $id)
  {
    $notice = Notice::findOrFail($id);
    $data = $this->validateNotice($request);
    if (($data['notice_type'] ?? '') === 'image' && !$request->hasFile('image') && (!$notice->image || $request->boolean('remove_image'))) {
      throw \Illuminate\Validation\ValidationException::withMessages(['image' => 'Image is required for Image Only notice.']);
    }

    if ($request->boolean('remove_image') && $notice->image) {
      $this->deleteNoticeImage($notice->image);
      $notice->image = null;
    }

    $newImage = $this->storeNoticeImage($request);
    if ($newImage) {
      if ($notice->image) $this->deleteNoticeImage($notice->image);
      $data['image'] = $newImage;
    } elseif ($request->boolean('remove_image')) {
      $data['image'] = null;
    }

    $notice->fill($data);
    $notice->save();
    Session::flash('success', 'Notice successfully updated.');
    return redirect()->route('noitice.edit');
  }

  private function validateNotice(Request $request): array
  {
    $data = $request->validate([
      'title' => 'required|string|max:255',
      'details' => 'nullable|string',
      'notice_type' => 'required|in:text,image,text_image',
      'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
      'url' => 'nullable|url|max:2048',
      'open_new_tab' => 'nullable|boolean',
      'show_on_website' => 'nullable|boolean',
      'display_duration' => 'required|integer|in:2,5,10,15,30',
      'start_at' => 'nullable|date',
      'end_at' => 'nullable|date|after_or_equal:start_at',
      'status' => 'required|in:Published,Unpublished',
    ]);

    $data['open_new_tab'] = $request->boolean('open_new_tab');
    $data['show_on_website'] = $request->boolean('show_on_website');
    return $data;
  }

  private function storeNoticeImage(Request $request): ?string
  {
    if (!$request->hasFile('image')) return null;
    $directory = public_path('uploads/notices');
    if (!is_dir($directory)) mkdir($directory, 0755, true);
    $file = $request->file('image');
    $name = 'notice_' . date('Ymd_His') . '_' . bin2hex(random_bytes(5)) . '.' . strtolower($file->getClientOriginalExtension());
    $file->move($directory, $name);
    return 'uploads/notices/' . $name;
  }

  private function deleteNoticeImage(?string $image): void
  {
    if (!$image || preg_match('/^https?:\/\//i', $image)) return;
    $path = public_path(ltrim($image, '/\\'));
    $root = realpath(public_path());
    $dir = realpath(dirname($path));
    if ($root && $dir && ($dir === $root || strpos($dir, $root . DIRECTORY_SEPARATOR) === 0) && is_file($path)) @unlink($path);
  }

  /** Admin-managed homepage hero banner settings. */
  public function heroBannerEdit()
  {
    $settings = [];
    if (Storage::exists('hero-banner.json')) {
      $settings = json_decode(Storage::get('hero-banner.json'), true) ?: [];
    }
    return view('layouts.homepagesettings.hero-banner', compact('settings'));
  }

  public function heroBannerUpdate(Request $request)
  {
    $current = [];
    if (Storage::exists('hero-banner.json')) {
      $current = json_decode(Storage::get('hero-banner.json'), true) ?: [];
    }

    $request->validate([
      'desktop_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
      'mobile_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
      'show_banner' => 'nullable|boolean',
      'remove_desktop_image' => 'nullable|boolean',
      'remove_mobile_image' => 'nullable|boolean',
    ]);

    if (!$request->hasFile('desktop_image') && empty($current['desktop_url']) && !$request->boolean('remove_desktop_image')) {
      return back()->withErrors(['desktop_image' => 'Please upload a desktop banner image.'])->withInput();
    }

    $settings = $current;
    if ($request->boolean('remove_desktop_image')) {
      $this->deleteHeroBannerImage($settings['desktop_path'] ?? null);
      $settings['desktop_path'] = null;
      $settings['desktop_url'] = null;
    }
    if ($request->boolean('remove_mobile_image')) {
      $this->deleteHeroBannerImage($settings['mobile_path'] ?? null);
      $settings['mobile_path'] = null;
      $settings['mobile_url'] = null;
    }

    foreach (['desktop_image' => 'desktop', 'mobile_image' => 'mobile'] as $field => $kind) {
      if ($request->hasFile($field)) {
        $oldPath = $settings[$kind . '_path'] ?? null;
        $file = $request->file($field);
        $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $directory = public_path('uploads/homepage-banners');
        if (!is_dir($directory)) mkdir($directory, 0755, true);
        $filename = 'hero_' . $kind . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
        $file->move($directory, $filename);
        $settings[$kind . '_path'] = 'uploads/homepage-banners/' . $filename;
        $settings[$kind . '_url'] = asset($settings[$kind . '_path']);
        if ($oldPath) $this->deleteHeroBannerImage($oldPath);
      }
    }

    $settings['show_banner'] = $request->boolean('show_banner');
    $settings['updated_at'] = now('Asia/Dhaka')->toDateTimeString();
    Storage::put('hero-banner.json', json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    return redirect()->route('settings.hero-banner')->with('success', 'Homepage hero banner updated successfully.');
  }

  private function deleteHeroBannerImage(?string $relativePath): void
  {
    if (!$relativePath || preg_match('/^https?:\\/\\//i', $relativePath)) return;
    $path = public_path(ltrim($relativePath, '/\\\\'));
    $root = realpath(public_path());
    $dir = realpath(dirname($path));
    if ($root && $dir && ($dir === $root || strpos($dir, $root . DIRECTORY_SEPARATOR) === 0) && is_file($path)) @unlink($path);
  }



  /** Admin editor for homepage social links and app-preview images. */
  public function socialSectionEdit()
  {
    $settings = [];
    if (Storage::exists('homepage-social.json')) {
      $settings = json_decode(Storage::get('homepage-social.json'), true) ?: [];
    }

    $defaults = [
      ['key' => 'facebook', 'name' => 'Live Education BD', 'label' => 'Facebook Page', 'url' => ''],
      ['key' => 'youtube', 'name' => 'Live Education BD', 'label' => 'YouTube', 'url' => 'https://www.youtube.com/channel/UClM04QBsh9WLeM0uQmqcASA'],
      ['key' => 'instagram', 'name' => 'Live Education BD', 'label' => 'Instagram', 'url' => ''],
      ['key' => 'linkedin', 'name' => 'Live Education BD', 'label' => 'LinkedIn', 'url' => ''],
      ['key' => 'whatsapp', 'name' => 'Live Education BD', 'label' => 'WhatsApp', 'url' => ''],
    ];
    $platforms = [];
    foreach ($defaults as $default) {
      $saved = $settings['platforms'][$default['key']] ?? [];
      $platforms[$default['key']] = array_merge($default, is_array($saved) ? $saved : []);
    }

    return view('layouts.homepagesettings.social-section', compact('settings', 'platforms'));
  }

  public function socialSectionUpdate(Request $request)
  {
    $request->validate([
      'platforms' => 'required|array',
      'platforms.*.name' => 'required|string|max:100',
      'platforms.*.label' => 'required|string|max:100',
      'platforms.*.url' => 'nullable|string|max:2048',
      'platforms.*.enabled' => 'nullable|boolean',
      'captions' => 'nullable|array',
      'captions.*' => 'nullable|string|max:120',
      'preview_images' => 'nullable|array',
      'preview_images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
      'remove_preview' => 'nullable|array',
      'remove_preview.*' => 'nullable|boolean',
    ]);

    $settings = [];
    if (Storage::exists('homepage-social.json')) {
      $settings = json_decode(Storage::get('homepage-social.json'), true) ?: [];
    }

    $platforms = [];
    foreach (['facebook', 'youtube', 'instagram', 'linkedin', 'whatsapp'] as $key) {
      $row = $request->input('platforms.' . $key, []);
      $url = trim((string) ($row['url'] ?? ''));
      if ($url !== '' && !filter_var($url, FILTER_VALIDATE_URL)) {
        return back()->withErrors(['platforms.' . $key . '.url' => 'Please enter a valid full URL including https://.'])->withInput();
      }
      $platforms[$key] = [
        'name' => trim((string) ($row['name'] ?? '')),
        'label' => trim((string) ($row['label'] ?? '')),
        'url' => $url,
        'enabled' => $request->boolean('platforms.' . $key . '.enabled'),
      ];
    }

    $images = $settings['images'] ?? [];
    $directory = public_path('uploads/homepage-social');
    if (!is_dir($directory)) mkdir($directory, 0755, true);

    foreach ([0, 1, 2] as $index) {
      $field = 'preview_images.' . $index;
      if ($request->hasFile($field)) {
        $oldPath = $images[$index]['path'] ?? null;
        $file = $request->file($field);
        $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $filename = 'social_preview_' . ($index + 1) . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
        $file->move($directory, $filename);
        $images[$index] = [
          'path' => 'uploads/homepage-social/' . $filename,
          'caption' => trim((string) $request->input('captions.' . $index, '')),
        ];
        $this->deleteSocialPreviewImage($oldPath);
      } elseif ($request->boolean('remove_preview.' . $index)) {
        $this->deleteSocialPreviewImage($images[$index]['path'] ?? null);
        $images[$index] = ['path' => null, 'caption' => trim((string) $request->input('captions.' . $index, ''))];
      } else {
        $images[$index]['caption'] = trim((string) $request->input('captions.' . $index, $images[$index]['caption'] ?? ''));
      }
    }

    $settings = [
      'platforms' => $platforms,
      'images' => $images,
      'updated_at' => now('Asia/Dhaka')->toDateTimeString(),
    ];
    Storage::put('homepage-social.json', json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

    return redirect()->route('settings.social-section')->with('success', 'Homepage social links and app previews updated successfully.');
  }

  private function deleteSocialPreviewImage(?string $relativePath): void
  {
    if (!$relativePath || preg_match('/^https?:\\/\\//i', $relativePath)) return;
    $path = public_path(ltrim($relativePath, '/\\\\'));
    $root = realpath(public_path());
    $dir = realpath(dirname($path));
    if ($root && $dir && ($dir === $root || strpos($dir, $root . DIRECTORY_SEPARATOR) === 0) && is_file($path)) @unlink($path);
  }

}
