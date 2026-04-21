# Form Helpers - Laravel 12

## Catatan Penting

Laravel 12 **tidak kompatibel** dengan Laravel Collective HTML (`laravelcollective/html`). Package tersebut hanya support hingga Laravel 10.

## Solusi: Blade Components Built-in

Laravel 12 sudah memiliki form helpers built-in melalui **Blade Components**. Tidak perlu install package tambahan.

## Cara Penggunaan

### 1. Menggunakan Blade Components

```blade
{{-- Form dengan method POST --}}
<form method="POST" action="{{ route('products.store') }}">
    @csrf
    
    <x-input-label for="name" :value="__('Nama Produk')" />
    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required autofocus />
    <x-input-error class="mt-2" :messages="$errors->get('name')" />
    
    <x-primary-button class="mt-4">
        {{ __('Simpan') }}
    </x-primary-button>
</form>
```

### 2. Komponen yang Tersedia

- `<x-text-input>` - Input text
- `<x-textarea>` - Textarea
- `<x-select>` - Select dropdown
- `<x-checkbox>` - Checkbox
- `<x-radio>` - Radio button
- `<x-input-label>` - Label
- `<x-input-error>` - Error message
- `<x-primary-button>` - Button primary
- `<x-secondary-button>` - Button secondary

### 3. Contoh Form Lengkap

```blade
<form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data">
    @csrf
    
    {{-- Nama Produk --}}
    <div>
        <x-input-label for="name" :value="__('Nama Produk')" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>
    
    {{-- Harga --}}
    <div class="mt-4">
        <x-input-label for="price" :value="__('Harga')" />
        <x-text-input id="price" name="price" type="number" step="0.01" class="mt-1 block w-full" :value="old('price')" required />
        <x-input-error class="mt-2" :messages="$errors->get('price')" />
    </div>
    
    {{-- Stok --}}
    <div class="mt-4">
        <x-input-label for="stock" :value="__('Stok')" />
        <x-text-input id="stock" name="stock" type="number" class="mt-1 block w-full" :value="old('stock')" required />
        <x-input-error class="mt-2" :messages="$errors->get('stock')" />
    </div>
    
    {{-- Status --}}
    <div class="mt-4">
        <x-input-label for="status" :value="__('Status')" />
        <x-select id="status" name="status" class="mt-1 block w-full">
            <option value="active">Aktif</option>
            <option value="inactive">Tidak Aktif</option>
        </x-select>
        <x-input-error class="mt-2" :messages="$errors->get('status')" />
    </div>
    
    {{-- Submit Button --}}
    <div class="flex items-center justify-end mt-4">
        <x-primary-button>
            {{ __('Simpan Produk') }}
        </x-primary-button>
    </div>
</form>
```

### 4. Form dengan Model Binding (Edit)

```blade
<form method="POST" action="{{ route('products.update', $product->id) }}">
    @csrf
    @method('PUT')
    
    <x-text-input id="name" name="name" type="text" :value="old('name', $product->name)" />
    
    {{-- ... field lainnya ... --}}
</form>
```

## Referensi

- [Laravel Blade Components Documentation](https://laravel.com/docs/12.x/blade#components)
- [Laravel Breeze Components](https://github.com/laravel/breeze/tree/2.x/stubs/default/resources/views/components)

## Catatan untuk Development

Semua form di project ini akan menggunakan Blade Components yang sudah built-in di Laravel 12. Tidak perlu install package tambahan untuk form helpers.









