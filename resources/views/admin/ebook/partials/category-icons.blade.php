<div class="ebi-control">
<label>Choose Category Icon *</label>
<p class="ebi-hint">আগের MCQ Job Preparation-এর মতো Icon নির্বাচন করুন। ছবি আপলোড করতে হবে না।</p>
<div class="ebi-icons">
@foreach($iconChoices as $iconName => $displayName)
@php $current = preg_replace('/^fa-/', '', (string) $savedIcon); @endphp
<label class="ebi-option {{ $current === $iconName ? 'is-selected' : '' }}">
 <input type="radio" data-ebi-choice value="{{ $iconName }}" {{ $current === $iconName ? 'checked' : '' }}>
 <i class="fa fa-{{ $iconName }}" aria-hidden="true"></i><small>{{ $displayName }}</small>
</label>
@endforeach
</div>
<label>Custom Icon Name (optional)</label>
<input class="ebook-field ebi-custom" data-ebi-icon name="icon" maxlength="80" pattern="fa-[a-z0-9-]+" required value="{{ preg_match('/^fa-[a-z0-9-]+$/', (string) $savedIcon) ? $savedIcon : 'fa-book' }}" placeholder="fa-book / fa-train">
<p class="ebi-hint">Icon Name: fa-book, fa-train ইত্যাদি। উপরের Icon-এ ক্লিক করলে এখানে স্বয়ংক্রিয়ভাবে বসবে।</p>
</div>
<div class="ebi-color-row ebi-control">
 <div><label>Icon Color</label><input type="color" name="icon_color" value="{{ $savedIconColor }}"></div>
 <div><label>Card Background Color</label><input type="color" name="card_color" value="{{ $savedCardColor }}"></div>
 <div><label>Live Category Preview</label><div class="ebi-preview" data-ebi-preview><i data-ebi-preview-icon class="fa fa-book"></i><span data-ebi-preview-name>{{ $previewName }}</span></div></div>
</div>
