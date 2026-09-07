@extends('layouts.admin')

@section('title', 'Edit Produk - Admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Edit Produk: {{ $product->name }}</h4>
                    <div>
                        <a href="{{ route('admin.products.show', $product->id) }}" class="btn btn-info me-2">
                            <i class="fas fa-eye"></i> Lihat
                        </a>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data" id="product-form">
                        @csrf
                        @method('PUT')
                        <!-- Topping Card dihapus dari form edit admin -->
                        <div class="row">
                            <div class="col-md-8">
                                <!-- Informasi Dasar -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="mb-0">Informasi Dasar</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Nama Produk <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $product->name) }}" required>
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="description" class="form-label">Deskripsi</label>
                                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4">{{ old('description', $product->description) }}</textarea>
                                            @error('description')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="category_id" class="form-label">Kategori <span class="text-danger">*</span></label>
                                                    <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                                                        <option value="">Pilih Kategori</option>
                                                        @foreach($categories as $category)
                                                            <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                                                {{ $category->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('category_id')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="price" class="form-label">Harga <span class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">Rp</span>
                                                        <input type="number" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price', $product->price) }}" min="0" step="0.01" required>
                                                        @error('price')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="stock" class="form-label">Stok <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control @error('stock') is-invalid @enderror" id="stock" name="stock" value="{{ old('stock', $product->stock) }}" min="0" required>
                                            @error('stock')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                {{-- Card Pilihan Saus dihapus --}}
                            </div>

                            <div class="col-md-4">
                                <!-- Gambar -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="mb-0">Gambar Produk</h5>
                                    </div>
                                    <div class="card-body">
                                        @if($product->image)
                                            <div class="mb-3 text-center">
                                                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="img-fluid rounded" style="max-height: 200px;">
                                                <div class="mt-2">
                                                    <small class="text-muted">Gambar saat ini</small>
                                                </div>
                                            </div>
                                        @endif
                                        <div class="mb-3">
                                            <label for="image" class="form-label">{{ $product->image ? 'Ganti Gambar' : 'Upload Gambar' }}</label>
                                            <input type="file" class="form-control @error('image') is-invalid @enderror" id="image" name="image" accept="image/*">
                                            @error('image')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">Format: JPG, PNG, GIF. Maksimal 2MB. {{ $product->image ? 'Kosongkan jika tidak ingin mengganti.' : '' }}</div>
                                        </div>
                                        <div id="image-preview" class="text-center" style="display: none;">
                                            <img id="preview-img" src="" alt="Preview" class="img-fluid rounded" style="max-height: 200px;">
                                            <div class="mt-2">
                                                <small class="text-muted">Preview gambar baru</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Status -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="mb-0">Status Produk</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_active">
                                                Produk Aktif
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1" {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_featured">
                                                Produk Unggulan
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tombol Aksi -->
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-grid gap-2">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save"></i> Update Produk
                                            </button>
                                            <a href="{{ route('admin.products.show', $product->id) }}" class="btn btn-info">
                                                <i class="fas fa-eye"></i> Lihat Produk
                                            </a>
                                            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                                                <i class="fas fa-times"></i> Batal
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Preview gambar
document.getElementById('image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview-img').src = e.target.result;
            document.getElementById('image-preview').style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        document.getElementById('image-preview').style.display = 'none';
    }
});

// Hapus manajemen topping & konversi JSON pada submit

// Format harga saat mengetik
document.getElementById('price').addEventListener('input', function(e) {
    let value = e.target.value.replace(/[^0-9]/g, '');
    if (value) {
        e.target.value = parseInt(value);
    }
});
</script>
@endpush