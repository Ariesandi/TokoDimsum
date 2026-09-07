@extends('layouts.app')

@section('title', 'Profil Saya - Dimsum Mamah Haura')

@section('content')
<div class="container py-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="display-5 fw-bold">
                <i class="fas fa-user-circle text-primary"></i> Profil Saya
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Profil Saya</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <!-- Profile Navigation -->
        <div class="col-lg-3 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="avatar-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" 
                             style="width: 80px; height: 80px; border-radius: 50%; font-size: 2rem;">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <h5 class="mt-2 mb-0">{{ auth()->user()->name }}</h5>
                        <small class="text-muted">{{ auth()->user()->email }}</small>
                    </div>
                    
                    <div class="list-group list-group-flush">
                        <a href="#profile-info" class="list-group-item list-group-item-action active" data-tab="profile-info">
                            <i class="fas fa-user"></i> Informasi Profil
                        </a>
                        <a href="#change-password" class="list-group-item list-group-item-action" data-tab="change-password">
                            <i class="fas fa-lock"></i> Ubah Kata Sandi
                        </a>
                        <a href="#order-history" class="list-group-item list-group-item-action" data-tab="order-history">
                            <i class="fas fa-history"></i> Riwayat Pesanan
                        </a>
                        <a href="#addresses" class="list-group-item list-group-item-action" data-tab="addresses">
                            <i class="fas fa-map-marker-alt"></i> Alamat Tersimpan
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Content -->
        <div class="col-lg-9">
            <!-- Profile Information Tab -->
            <div class="tab-content" id="profile-info">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-user"></i> Profile Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="profile-form" method="POST" action="{{ route('profile.update') }}">
                            @csrf
                            @method('PUT')
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Full Name *</label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="{{ old('name', auth()->user()->name) }}" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email Address *</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="{{ old('email', auth()->user()->email) }}" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Nomor Telepon</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="{{ old('phone', auth()->user()->phone) }}" 
                                           placeholder="e.g., 08123456789">
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="birth_date" class="form-label">Date of Birth</label>
                                    <input type="date" class="form-control" id="birth_date" name="birth_date" 
                                           value="{{ old('birth_date', auth()->user()->birth_date) }}">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="address" class="form-label">Address</label>
                                <textarea class="form-control" id="address" name="address" rows="3" 
                                          placeholder="Enter your full address">{{ old('address', auth()->user()->address) }}</textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="city" class="form-label">Kota</label>
                                    <input type="text" class="form-control" id="city" name="city" 
                                           value="{{ old('city', auth()->user()->city) }}" 
                                           placeholder="contoh: Jakarta">
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="postal_code" class="form-label">Kode Pos</label>
                                    <input type="text" class="form-control" id="postal_code" name="postal_code" 
                                           value="{{ old('postal_code', auth()->user()->postal_code) }}" 
                                           placeholder="contoh: 12345">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Perbarui Profil
                                </button>
                                <button type="reset" class="btn btn-outline-secondary">
                                    <i class="fas fa-undo"></i> Atur Ulang
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Change Password Tab -->
            <div class="tab-content d-none" id="change-password">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-lock"></i> Change Password
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="password-form" method="POST" action="{{ route('profile.password') }}">
                            @csrf
                            @method('PUT')
                            
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Current Password *</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('current_password')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="new_password" class="form-label">New Password *</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="new_password" name="password" required minlength="8">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('new_password')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="form-text">Kata sandi harus minimal 8 karakter.</div>
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Confirm New Password *</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password_confirmation')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <strong>Password Requirements:</strong>
                                <ul class="mb-0 mt-2">
                                    <li>Minimal 8 karakter</li>
                                    <li>Mengandung huruf besar dan huruf kecil</li>
                                    <li>Mengandung setidaknya satu angka</li>
                                    <li>Mengandung setidaknya satu karakter khusus</li>
                                </ul>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-key"></i> Ubah Kata Sandi
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Order History Tab -->
            <div class="tab-content d-none" id="order-history">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-history"></i> Recent Orders
                        </h5>
                        <a href="{{ route('orders.index') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-list"></i> View All Orders
                        </a>
                    </div>
                    <div class="card-body">
                        @if(auth()->user()->orders && auth()->user()->orders->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Order ID</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Total</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach(auth()->user()->orders->take(5) as $order)
                                        <tr>
                                            <td><strong>#{{ $order->id }}</strong></td>
                                            <td>{{ $order->created_at->format('d M Y') }}</td>
                                            <td>
                                                @php
                                                    $statusColors = [
                                                        'pending' => 'warning',
                                                        'confirmed' => 'info',
                                                        'processing' => 'primary',
                                                        'shipped' => 'success',
                                                        'delivered' => 'success',
                                                        'cancelled' => 'danger'
                                                    ];
                                                @endphp
                                                <span class="badge bg-{{ $statusColors[$order->status] ?? 'secondary' }}">
                                                    {{ ucfirst($order->status) }}
                                                </span>
                                            </td>
                                            <td><strong>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</strong></td>
                                            <td>
                                                <a href="{{ route('orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i> Lihat
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Belum ada pesanan</h5>
                                <p class="text-muted">Mulai berbelanja untuk melihat pesanan Anda di sini.</p>
                                <a href="{{ route('products.index') }}" class="btn btn-primary">
                                    <i class="fas fa-shopping-bag"></i> Mulai Belanja
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Saved Addresses Tab -->
            <div class="tab-content d-none" id="addresses">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-map-marker-alt"></i> Saved Addresses
                        </h5>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                            <i class="fas fa-plus"></i> Tambah Alamat
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="addresses-list">
                            <!-- Addresses will be loaded here -->
                            <div class="text-center py-4">
                                <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                                <p class="text-muted mt-2">Memuat alamat...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Address Modal -->
<div class="modal fade" id="addAddressModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Alamat Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="address-form">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="address_label" class="form-label">Label Alamat *</label>
                        <input type="text" class="form-control" id="address_label" name="label" 
                               placeholder="misal: Rumah, Kantor" required>
                    </div>
                    <div class="mb-3">
                        <label for="recipient_name" class="form-label">Nama Penerima *</label>
                        <input type="text" class="form-control" id="recipient_name" name="recipient_name" 
                               value="{{ auth()->user()->name }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="recipient_phone" class="form-label">Nomor Telepon *</label>
                        <input type="tel" class="form-control" id="recipient_phone" name="recipient_phone" 
                               value="{{ auth()->user()->phone }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="full_address" class="form-label">Alamat Lengkap *</label>
                        <textarea class="form-control" id="full_address" name="address" rows="3" 
                                  placeholder="Masukkan alamat lengkap" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="address_city" class="form-label">Kota *</label>
                            <input type="text" class="form-control" id="address_city" name="city" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="address_postal_code" class="form-label">Kode Pos *</label>
                            <input type="text" class="form-control" id="address_postal_code" name="postal_code" required>
                        </div>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_default" name="is_default">
                        <label class="form-check-label" for="is_default">
                            Set as default address
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Alamat
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Tab navigation
    $('.list-group-item-action').on('click', function(e) {
        e.preventDefault();
        
        // Remove active class from all tabs
        $('.list-group-item-action').removeClass('active');
        $('.tab-content').addClass('d-none');
        
        // Add active class to clicked tab
        $(this).addClass('active');
        
        // Show corresponding content
        const tabId = $(this).data('tab');
        $('#' + tabId).removeClass('d-none');
        
        // Load addresses if addresses tab is clicked
        if (tabId === 'addresses') {
            loadAddresses();
        }
    });
    
    // Profile form submission
    $('#profile-form').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Updating...').prop('disabled', true);
        
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    showAlert('success', 'Profile updated successfully!');
                    // Update name in navigation
                    $('.card-body h5').text(response.user.name);
                } else {
                    showAlert('danger', response.message || 'Error updating profile');
                }
                submitBtn.html(originalText).prop('disabled', false);
            },
            error: function(xhr) {
                handleFormErrors(xhr, form);
                submitBtn.html(originalText).prop('disabled', false);
            }
        });
    });
    
    // Password form submission
    $('#password-form').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Changing...').prop('disabled', true);
        
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    showAlert('success', 'Password changed successfully!');
                    form[0].reset();
                } else {
                    showAlert('danger', response.message || 'Error changing password');
                }
                submitBtn.html(originalText).prop('disabled', false);
            },
            error: function(xhr) {
                handleFormErrors(xhr, form);
                submitBtn.html(originalText).prop('disabled', false);
            }
        });
    });
    
    // Address form submission
    $('#address-form').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Saving...').prop('disabled', true);
        
        $.ajax({
            url: '/profile/addresses',
            method: 'POST',
            data: form.serialize() + '&_token={{ csrf_token() }}',
            success: function(response) {
                if (response.success) {
                    showAlert('success', 'Address saved successfully!');
                    $('#addAddressModal').modal('hide');
                    form[0].reset();
                    loadAddresses();
                } else {
                    showAlert('danger', response.message || 'Error saving address');
                }
                submitBtn.html(originalText).prop('disabled', false);
            },
            error: function(xhr) {
                handleFormErrors(xhr, form);
                submitBtn.html(originalText).prop('disabled', false);
            }
        });
    });
    
    function loadAddresses() {
        $('#addresses-list').html(`
            <div class="text-center py-4">
                <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                <p class="text-muted mt-2">Loading addresses...</p>
            </div>
        `);
        
        $.ajax({
            url: '/profile/addresses',
            method: 'GET',
            success: function(response) {
                if (response.addresses && response.addresses.length > 0) {
                    let html = '';
                    response.addresses.forEach(function(address) {
                        html += `
                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">
                                                ${address.label}
                                                ${address.is_default ? '<span class="badge bg-primary ms-2">Utama</span>' : ''}
                                            </h6>
                                            <p class="mb-1"><strong>${address.recipient_name}</strong></p>
                                            <p class="mb-1">${address.address}</p>
                                            <p class="mb-1">${address.city}, ${address.postal_code}</p>
                                            <p class="mb-0 text-muted">${address.recipient_phone}</p>
                                        </div>
                                        <div class="dropdown">
                                            <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="dropdown">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#" onclick="editAddress(${address.id})"><i class="fas fa-edit me-2"></i>Ubah</a></li>
                                                ${!address.is_default ? `<li><a class="dropdown-item" href="#" onclick="setDefaultAddress(${address.id})"><i class="fas fa-star me-2"></i>Jadikan Utama</a></li>` : ''}
                                                <li><a class="dropdown-item text-danger" href="#" onclick="deleteAddress(${address.id})"><i class="fas fa-trash me-2"></i>Hapus</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    $('#addresses-list').html(html);
                } else {
                    $('#addresses-list').html(`
                        <div class="text-center py-4">
                            <i class="fas fa-map-marker-alt fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No saved addresses</h5>
                            <p class="text-muted">Tambahkan alamat pertama Anda untuk membuat proses checkout lebih cepat.</p>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                                <i class="fas fa-plus"></i> Tambah Alamat
                            </button>
                        </div>
                    `);
                }
            },
            error: function() {
                $('#addresses-list').html(`
                    <div class="text-center py-4">
                        <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                        <h5 class="text-danger">Error loading addresses</h5>
                        <button class="btn btn-outline-primary" onclick="loadAddresses()">
                            <i class="fas fa-redo"></i> Coba Lagi
                        </button>
                    </div>
                `);
            }
        });
    }
    
    function handleFormErrors(xhr, form) {
        // Clear previous errors
        form.find('.is-invalid').removeClass('is-invalid');
        form.find('.invalid-feedback').text('');
        
        if (xhr.status === 422) {
            const errors = xhr.responseJSON.errors;
            Object.keys(errors).forEach(function(field) {
                const input = form.find(`[name="${field}"]`);
                input.addClass('is-invalid');
                input.siblings('.invalid-feedback').text(errors[field][0]);
            });
        } else {
            const message = xhr.responseJSON?.message || 'An error occurred';
            showAlert('danger', message);
        }
    }
    
    function showAlert(type, message) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999; max-width: 350px;" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        $(alertHtml).appendTo('body');
        
        setTimeout(function() {
            $('.alert').alert('close');
        }, 4000);
    }
    
    // Make functions global
    window.loadAddresses = loadAddresses;
    window.showAlert = showAlert;
});

// Toggle password visibility
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const button = field.nextElementSibling;
    const icon = button.querySelector('i');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Address management functions
function editAddress(id) {
    // Implementation for editing address
    console.log('Edit address:', id);
}

function setDefaultAddress(id) {
    $.ajax({
        url: `/profile/addresses/${id}/default`,
        method: 'POST',
        data: { _token: '{{ csrf_token() }}' },
        success: function(response) {
            if (response.success) {
                showAlert('success', 'Default address updated!');
                loadAddresses();
            }
        }
    });
}

function deleteAddress(id) {
    if (confirm('Apakah Anda yakin ingin menghapus alamat ini?')) {
        $.ajax({
            url: `/profile/addresses/${id}`,
            method: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: function(response) {
                if (response.success) {
                    showAlert('success', 'Alamat utama berhasil diperbarui!');
                    loadAddresses();
                }
            }
        });
    }
}
</script>
@endpush