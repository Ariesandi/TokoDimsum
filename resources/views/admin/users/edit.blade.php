@extends('layouts.admin')

@section('title', 'Edit Pengguna - ' . $user->name)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Edit Pengguna</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Pengguna</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.users.show', $user) }}">{{ $user->name }}</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-info me-2">
                <i class="fas fa-eye"></i> Lihat Detail
            </a>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Edit Informasi Pengguna</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.users.update', $user) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Password Baru</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                       id="password" name="password">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Kosongkan jika tidak ingin mengubah password</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                                <input type="password" class="form-control" 
                                       id="password_confirmation" name="password_confirmation">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Nomor Telepon</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                                <select class="form-control @error('role') is-invalid @enderror" id="role" name="role" required>
                                    <option value="">Pilih Role</option>
                                    <option value="customer" {{ old('role', $user->role) == 'customer' ? 'selected' : '' }}>Customer</option>
                                    <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                                </select>
                                @error('role')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Alamat</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                      id="address" name="address" rows="3">{{ old('address', $user->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="avatar" class="form-label">Avatar</label>
                            
                            @if($user->avatar)
                                <div class="mb-2">
                                    <img src="{{ Storage::url($user->avatar) }}" 
                                         alt="Current Avatar" class="img-thumbnail" style="max-width: 150px;">
                                    <div class="mt-1">
                                        <small class="text-muted">Avatar saat ini</small>
                                    </div>
                                </div>
                            @endif
                            
                            <input type="file" class="form-control @error('avatar') is-invalid @enderror" 
                                   id="avatar" name="avatar" accept="image/*">
                            @error('avatar')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Format yang didukung: JPEG, PNG, JPG, GIF. Maksimal 2MB. Kosongkan jika tidak ingin mengubah avatar.</small>
                        </div>

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-secondary me-2">Batal</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Pengguna
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Current User Info -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Saat Ini</h6>
                </div>
                <div class="card-body text-center">
                    @if($user->avatar)
                        <img src="{{ Storage::url($user->avatar) }}" 
                             alt="Avatar" class="rounded-circle mb-3" width="80" height="80">
                    @else
                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" 
                             style="width: 80px; height: 80px; color: white; font-size: 32px; font-weight: bold;">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    @endif
                    
                    <h6 class="font-weight-bold">{{ $user->name }}</h6>
                    <p class="text-muted mb-2">{{ $user->email }}</p>
                    
                    @if($user->role == 'admin')
                        <span class="badge badge-danger mb-2">Admin</span>
                    @else
                        <span class="badge badge-primary mb-2">Customer</span>
                    @endif
                    
                    <div class="mt-3">
                        <small class="text-muted d-block">Bergabung: {{ $user->created_at->format('d F Y') }}</small>
                        <small class="text-muted d-block">Update terakhir: {{ $user->updated_at->format('d F Y') }}</small>
                    </div>
                </div>
            </div>

            <!-- Edit Guidelines -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Panduan Edit</h6>
                </div>
                <div class="card-body">
                    <h6 class="font-weight-bold">Tips Edit Pengguna:</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-info-circle text-info me-2"></i>
                            Email harus tetap unik
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-info-circle text-info me-2"></i>
                            Password kosong = tidak diubah
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-info-circle text-info me-2"></i>
                            Avatar baru akan mengganti yang lama
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-warning text-warning me-2"></i>
                            Hati-hati mengubah role admin
                        </li>
                    </ul>
                    
                    <hr>
                    
                    <h6 class="font-weight-bold">Statistik Pengguna:</h6>
                    <div class="mb-2">
                        <strong>Total Pesanan:</strong> {{ $user->transactions()->count() }}
                    </div>
                    <div class="mb-2">
                        <strong>Total Belanja:</strong> Rp {{ number_format($user->transactions()->where('status', 'completed')->sum('total_amount'), 0, ',', '.') }}
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Aksi Cepat</h6>
                </div>
                <div class="card-body">
                    @if($user->role != 'admin')
                        <form action="{{ route('admin.users.reset-password', $user) }}" method="POST" class="mb-2">
                            @csrf
                            <button type="submit" class="btn btn-warning btn-sm w-100" 
                                    onclick="return confirm('Yakin ingin reset password pengguna ini?')">
                                <i class="fas fa-key"></i> Reset Password
                            </button>
                        </form>
                        
                        <form action="{{ route('admin.users.toggle-role', $user) }}" method="POST" class="mb-2">
                            @csrf
                            <button type="submit" class="btn btn-info btn-sm w-100" 
                                    onclick="return confirm('Yakin ingin mengubah role pengguna ini?')">
                                <i class="fas fa-user-shield"></i> 
                                {{ $user->role == 'admin' ? 'Jadikan Customer' : 'Jadikan Admin' }}
                            </button>
                        </form>
                    @endif
                    
                    <a href="{{ route('admin.orders.index', ['search' => $user->email]) }}" 
                       class="btn btn-outline-primary btn-sm w-100">
                        <i class="fas fa-shopping-cart"></i> Lihat Pesanan
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Preview new avatar
    $('#avatar').change(function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                // Create or update preview
                if (!$('#avatar-preview').length) {
                    $('#avatar').after('<div id="avatar-preview" class="mt-2"><img src="" class="img-thumbnail" style="max-width: 150px;"><div class="mt-1"><small class="text-muted">Preview avatar baru</small></div></div>');
                }
                $('#avatar-preview img').attr('src', e.target.result);
            };
            reader.readAsDataURL(file);
        } else {
            $('#avatar-preview').remove();
        }
    });

    // Password strength indicator
    $('#password').on('input', function() {
        const password = $(this).val();
        
        // Remove existing indicator
        $('#password-strength').remove();
        
        if (password.length > 0) {
            let strength = 0;
            
            if (password.length >= 8) strength++;
            if (password.match(/[a-z]/)) strength++;
            if (password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;
            
            let strengthText = '';
            let strengthClass = '';
            
            switch(strength) {
                case 0:
                case 1:
                    strengthText = 'Lemah';
                    strengthClass = 'text-danger';
                    break;
                case 2:
                case 3:
                    strengthText = 'Sedang';
                    strengthClass = 'text-warning';
                    break;
                case 4:
                case 5:
                    strengthText = 'Kuat';
                    strengthClass = 'text-success';
                    break;
            }
            
            $(this).after(`<small id="password-strength" class="${strengthClass}">Kekuatan password: ${strengthText}</small>`);
        }
    });

    // Confirm role change
    $('#role').change(function() {
        const currentRole = '{{ $user->role }}';
        const newRole = $(this).val();
        
        if (currentRole === 'admin' && newRole === 'customer') {
            if (!confirm('Anda akan mengubah admin menjadi customer. Yakin melanjutkan?')) {
                $(this).val(currentRole);
            }
        } else if (currentRole === 'customer' && newRole === 'admin') {
            if (!confirm('Anda akan memberikan akses admin kepada pengguna ini. Yakin melanjutkan?')) {
                $(this).val(currentRole);
            }
        }
    });
});
</script>
@endpush
@endsection