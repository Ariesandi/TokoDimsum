@extends('layouts.admin')

@section('title', 'Edit Kategori - Admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Edit Kategori: {{ $category->name }}</h4>
                    <div>
                        <a href="{{ route('admin.categories.show', $category->id) }}" class="btn btn-info me-2">
                            <i class="fas fa-eye"></i> Lihat
                        </a>
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
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

                    <form action="{{ route('admin.categories.update', $category->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $category->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Deskripsi</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" name="description" rows="4">{{ old('description', $category->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="image" class="form-label">Gambar Kategori</label>
                                    <input type="file" class="form-control @error('image') is-invalid @enderror" 
                                           id="image" name="image" accept="image/*">
                                    @error('image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Format yang didukung: JPG, PNG, GIF. Maksimal 2MB. Kosongkan jika tidak ingin mengubah gambar.</div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Gambar Saat Ini</label>
                                    @if($category->image)
                                        <div id="current-image" class="border rounded p-3 text-center mb-3">
                                            <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}" class="img-fluid" style="max-height: 200px;">
                                            <p class="text-muted mt-2 mb-0">Gambar saat ini</p>
                                        </div>
                                    @else
                                        <div id="current-image" class="border rounded p-3 text-center bg-light mb-3">
                                            <i class="fas fa-image fa-3x text-muted mb-2"></i>
                                            <p class="text-muted mb-0">Tidak ada gambar</p>
                                        </div>
                                    @endif
                                    
                                    <div id="image-preview" class="border rounded p-3 text-center" style="display: none;">
                                        <img id="preview-img" src="" alt="Preview" class="img-fluid" style="max-height: 200px;">
                                        <p class="text-muted mt-2 mb-0">Preview gambar baru</p>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Kategori Aktif
                                        </label>
                                    </div>
                                    <div class="form-text">Kategori aktif akan ditampilkan di website</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Update Kategori
                                    </button>
                                    <a href="{{ route('admin.categories.show', $category->id) }}" class="btn btn-info">
                                        <i class="fas fa-eye"></i> Lihat Kategori
                                    </a>
                                    <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Batal
                                    </a>
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
</script>
@endpush