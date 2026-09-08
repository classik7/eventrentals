@extends('layouts.app')

@php
    $breadcrumbs = '<a href="'.route('items.index').'">Home</a> / <a href="'.route('items.my').'">My Items</a> / Edit Item';
@endphp

@section('content')
<style>
    .apple-container {
        min-height: calc(100vh - 120px);
        display: flex;
        justify-content: center;
        align-items: flex-start;
        padding: 40px 20px;
    }

    .apple-form {
        width: 100%;
        max-width: 900px;
        background: linear-gradient(
            180deg,
            rgba(255,255,255,0.95),
            rgba(255,255,255,0.85)
        );
        backdrop-filter: blur(18px);
        border-radius: 28px;
        padding: 48px;
        box-shadow:
            0 30px 60px rgba(0,0,0,0.12),
            inset 0 1px 0 rgba(255,255,255,0.6);
    }

    .apple-title {
        font-size: 32px;
        font-weight: 700;
        margin-bottom: 6px;
        letter-spacing: -0.5px;
    }

    .apple-subtitle {
        color: #6b7280;
        margin-bottom: 36px;
        font-size: 16px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 28px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-group.full {
        grid-column: span 2;
    }

    label {
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 8px;
        color: #374151;
    }

    input,
    select,
    textarea {
        padding: 14px 16px;
        border-radius: 14px;
        border: 1px solid #d1d5db;
        font-size: 15px;
        background: rgba(255,255,255,0.9);
        transition: all 0.25s ease;
    }

    textarea {
        resize: vertical;
        min-height: 120px;
    }

    input:focus,
    select:focus,
    textarea:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow:
            0 0 0 4px rgba(37,99,235,0.15),
            inset 0 1px 2px rgba(0,0,0,0.05);
    }

    .image-preview {
        margin-top: 10px;
    }

    .image-preview img {
        width: 160px;
        height: 120px;
        object-fit: cover;
        border-radius: 12px;
        box-shadow: 0 6px 16px rgba(0,0,0,0.15);
    }

    .apple-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 40px;
        flex-wrap: wrap;
        gap: 16px;
    }

    .primary-btn {
        background: linear-gradient(180deg, #2563eb, #1e40af);
        padding: 14px 28px;
        border-radius: 999px;
        color: white;
        font-size: 15px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        box-shadow: 0 10px 25px rgba(37,99,235,0.35);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .primary-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 14px 32px rgba(37,99,235,0.45);
    }

    .secondary-link {
        color: #6b7280;
        font-size: 14px;
        text-decoration: none;
    }

    .secondary-link:hover {
        text-decoration: underline;
    }

    @media (max-width: 768px) {
        .apple-form {
            padding: 32px 24px;
        }

        .form-grid {
            grid-template-columns: 1fr;
        }

        .form-group.full {
            grid-column: span 1;
        }
    }
</style>

<div class="apple-container">
    <form
    method="POST"
    action="{{ route('items.update', $item) }}"
    enctype="multipart/form-data"
    class="apple-form"
>
    @csrf
    @method('PUT')

    <div class="apple-title">Edit Item</div>
    <div class="apple-subtitle">
        Update your item details. Changes reflect immediately.
    </div>

    <div class="form-grid">
        <!-- TITLE -->
        <div class="form-group full">
            <label>Item Name</label>
            <input
                type="text"
                name="title"
                value="{{ old('title', $item->title) }}"
                required
            >
        </div>

        <!-- DESCRIPTION -->
        <div class="form-group full">
            <label>Description</label>
            <textarea
                name="description"
                required
            >{{ old('description', $item->description) }}</textarea>
        </div>

        <!-- CATEGORY -->
        <div class="form-group">
            <label>Category</label>
            <select name="category_id" required>
                @foreach($categories as $category)
                    <option
                        value="{{ $category->id }}"
                        @selected($item->category_id == $category->id)
                    >
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- LISTING TYPE -->
        <div class="form-group">
            <label>Listing Type</label>
            <select name="listing_type" required>
                <option value="rent" @selected($item->is_rentable && !$item->is_sellable)>Rent Only</option>
                <option value="sell" @selected(!$item->is_rentable && $item->is_sellable)>Sell Only</option>
                <option value="both" @selected($item->is_rentable && $item->is_sellable)>Rent & Sell</option>
            </select>
        </div>

        <!-- PRICE PER DAY -->
        <div class="form-group">
            <label>Price per Day (₦)</label>
            <input
                type="number"
                name="price_per_day"
                value="{{ old('price_per_day', $item->price_per_day) }}"
                min="0"
            >
        </div>

        <!-- SELLING PRICE -->
        <div class="form-group">
            <label>Selling Price (₦)</label>
            <input
                type="number"
                name="selling_price"
                value="{{ old('selling_price', $item->selling_price) }}"
                min="0"
            >
        </div>

        <!-- ORIGINAL PRICE -->
        <div class="form-group">
            <label>Original Price (₦)</label>
            <input
                type="number"
                name="original_price"
                value="{{ old('original_price', $item->original_price) }}"
                min="0"
            >
        </div>

        <!-- QUANTITY UNITS -->
        <div class="form-group">
            <label>Quantity Available</label>
            <input
                type="number"
                name="quantity_units"
                value="{{ old('quantity_units', $item->quantity_units) }}"
                min="1"
                required
            >
        </div>

        <!-- UNIT SIZE -->
        <div class="form-group">
            <label>Units per Package</label>
            <input
                type="number"
                name="unit_size"
                value="{{ old('unit_size', $item->unit_size) }}"
                min="1"
            >
        </div>

        <!-- UNIT LABEL -->
        <div class="form-group">
            <label>Unit Label</label>
            <input
                type="text"
                name="unit_label"
                value="{{ old('unit_label', $item->unit_label) }}"
                placeholder="e.g. dozen, pack"
            >
        </div>

        <!-- LOCATION -->
        <div class="form-group full">
            <label>Location</label>
            <input
                type="text"
                name="location"
                value="{{ old('location', $item->location) }}"
                required
            >
        </div>

        <!-- STATE -->
        <div class="form-group">
            <label>State</label>
            <select name="state" required>
                <option value="">Select State</option>
                @foreach(["Abia","Adamawa","Akwa Ibom","Anambra","Bauchi","Bayelsa","Benue","Borno","Cross River","Delta","Ebonyi","Edo","Ekiti","Enugu","FCT","Gombe","Imo","Jigawa","Kaduna","Kano","Katsina","Kebbi","Kogi","Kwara","Lagos","Nasarawa","Niger","Ogun","Ondo","Osun","Oyo","Plateau","Rivers","Sokoto","Taraba","Yobe","Zamfara"] as $state)
                    <option value="{{ $state }}" @selected($item->state == $state)>{{ $state }}</option>
                @endforeach
            </select>
        </div>

        <!-- LOCAL GOVERNMENT -->
        <div class="form-group">
            <label>Local Government</label>
            <input
                type="text"
                name="local_government"
                value="{{ old('local_government', $item->local_government) }}"
                required
            >
        </div>

       
        <!-- CURRENT IMAGES WITH DELETE OPTION -->
<!-- CURRENT IMAGES WITH DELETE OPTION -->
<div class="form-group full">
    <label>Current Images (Click X to remove)</label>
    
    @if($item->images->count())
        <div style="display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 16px;">
            @foreach($item->images as $image)
                <div style="position: relative; display: inline-block; width: 96px; height: 96px;">
                    
                    <!-- Image -->
                    <img src="{{ Storage::url($image->image) }}" 
                         id="img_{{ $image->id }}"
                         style="width: 96px; height: 96px; object-fit: cover; border-radius: 8px; border: 3px solid transparent; transition: all 0.2s;">
                    
                    <!-- Hidden Checkbox -->
                    <input type="checkbox" 
                           name="delete_images[]" 
                           value="{{ $image->id }}" 
                           id="checkbox_{{ $image->id }}"
                           style="position: absolute; opacity: 0; pointer-events: none;">
                    
                    <!-- Clickable X Button -->
                    <button type="button" 
                            onclick="toggleDelete({{ $image->id }})"
                            style="position: absolute; top: -8px; right: -8px; width: 24px; height: 24px; background: #dc2626; color: white; border: none; border-radius: 50%; cursor: pointer; font-size: 16px; font-weight: bold; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(0,0,0,0.3); z-index: 10;">
                        ×
                    </button>
                    
                </div>
            @endforeach
        </div>
        
        <p style="font-size: 12px; color: #666; margin-bottom: 16px;">
            Click the red X to mark images for deletion. Selected images will be removed when you click "Update Item".
        </p>
        
        <!-- JavaScript to handle toggling -->
        <script>
            function toggleDelete(imageId) {
                const checkbox = document.getElementById('checkbox_' + imageId);
                const img = document.getElementById('img_' + imageId);
                
                checkbox.checked = !checkbox.checked;
                
                if (checkbox.checked) {
                    img.style.borderColor = '#dc2626';
                    img.style.opacity = '0.5';
                } else {
                    img.style.borderColor = 'transparent';
                    img.style.opacity = '1';
                }
            }
        </script>
    @else
        <p style="color: #666; font-size: 14px;">No images uploaded yet.</p>
    @endif

    <label style="display: block; margin-top: 16px; font-weight: 600;">Add New Images</label>
    <input type="file" name="images[]" multiple accept="image/*" style="margin-top: 8px; padding: 8px; border: 1px solid #d1d5db; border-radius: 8px; width: 100%;">
</div>

    <div class="apple-actions">
        <button type="submit" class="primary-btn">
            Update Item
        </button>

        <a href="{{ route('items.my') }}" class="secondary-link">
            Cancel & return to My Items
        </a>
    </div>
</form>
</div>
@endsection
