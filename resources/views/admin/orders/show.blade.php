@extends('layouts.admin')

@section('title', 'Detail Transaksi - Admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Detail Transaksi #{{ $order->id }}</h4>
                    <div>
                        <button class="btn btn-primary" onclick="printOrder()">
                            <i class="fas fa-print"></i> Cetak
                        </button>
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="row">
                        <!-- Order Information -->
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">Informasi Pesanan</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td width="150"><strong>ID Pesanan:</strong></td>
                                            <td>#{{ $order->id }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Tanggal:</strong></td>
                                            <td>{{ $order->created_at->format('d M Y H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Status:</strong></td>
                                            <td>
                                                @php
                                                    $statusColors = [
                                                        'pending' => 'warning',
                                                        'confirmed' => 'info',
                                                        'processing' => 'primary',
                                                        'ready' => 'success',
                                                        'completed' => 'success',
                                                        'cancelled' => 'danger'
                                                    ];
                                                    $statusColor = $statusColors[$order->status] ?? 'secondary';
                                                @endphp
                                                <span class="badge bg-{{ $statusColor }} fs-6">{{ ucfirst($order->status) }}</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Status Pembayaran:</strong></td>
                                            <td>
                                                @if($order->payment_status)
                                                    @php
                                                        $paymentColors = [
                                                            'pending' => 'warning',
                                                            'verified' => 'success',
                                                            'rejected' => 'danger'
                                                        ];
                                                        $paymentColor = $paymentColors[$order->payment_status] ?? 'secondary';
                                                    @endphp
                                                    <span class="badge bg-{{ $paymentColor }} fs-6">
                                                        {{ ucfirst($order->payment_status) }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary fs-6">Tidak tersedia</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @if($order->tracking_number)
                                        <tr>
                                            <td><strong>Nomor Resi:</strong></td>
                                            <td><strong class="text-success">{{ $order->tracking_number }}</strong></td>
                                        </tr>
                                        @endif
                                        <tr>
                                            <td><strong>Metode Pembayaran:</strong></td>
                                            <td>{{ $order->payment_method ?? 'Tidak tersedia' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Total Belanja:</strong></td>
                                            <td><strong class="text-primary">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</strong></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <!-- Payment Proof -->
                            @if($order->payment_proof)
                            <div class="card mb-4">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Bukti Pembayaran</h5>
                                    <a href="{{ asset('storage/' . $order->payment_proof) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-external-link-alt"></i> Lihat Asli
                                    </a>
                                </div>
                                <div class="card-body">
                                    <img src="{{ asset('storage/' . $order->payment_proof) }}" alt="Bukti Pembayaran" class="img-fluid rounded" style="max-height: 400px; object-fit: contain;">
                                </div>
                            </div>
                            @endif
                        </div>

                        <!-- Customer Information -->
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">Informasi Pelanggan</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td width="150"><strong>Nama:</strong></td>
                                            <td>{{ $order->customer_name ?? $order->user->name ?? 'Tidak tersedia' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Email:</strong></td>
                                            <td>{{ $order->customer_email ?? $order->user->email ?? 'Tidak tersedia' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Telepon:</strong></td>
                                            <td>{{ $order->customer_phone ?? optional($order->user)->phone ?? 'Tidak tersedia' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Alamat:</strong></td>
                                            <td>
                                                @php
                                                    $alamat = $order->customer_address ?? optional($order->user)->address;
                                                @endphp
                                                {{ ($alamat && trim($alamat) !== '') ? $alamat : '-' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Catatan:</strong></td>
                                            <td>
                                                @if(!empty($order->notes))
                                                    {!! nl2br(e(str_replace('\\n', "\n", $order->notes))) !!}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Item Pesanan</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Produk</th>
                                            <th>Harga</th>
                                            <th>Jumlah</th>
                                            <th>Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($order->transactionDetails as $detail)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($detail->product->image)
                                                        <img src="{{ asset('storage/' . $detail->product->image) }}" alt="{{ $detail->product->name }}" class="me-3" style="width: 60px; height: 45px; object-fit: cover; border-radius: 5px;">
                                                    @else
                                                        <div class="bg-light me-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 45px; border-radius: 5px;">
                                                            <i class="fas fa-image text-muted"></i>
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <strong>{{ $detail->product->name }}</strong><br>
                                                        <small class="text-muted">{{ optional($detail->product->category)->name ?? 'Tidak Berkategori' }}</small>
                                                        @if(!empty($detail->product->description))
                                                            <br><small class="text-muted">{{ Str::limit($detail->product->description, 60) }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>Rp {{ number_format($detail->unit_price ?? $detail->price ?? optional($detail->product)->price ?? 0, 0, ',', '.') }}</td>
                                            <td>{{ $detail->quantity }}</td>
                                            <td>Rp {{ number_format(($detail->subtotal ?? (($detail->unit_price ?? $detail->price ?? optional($detail->product)->price ?? 0) * $detail->quantity)), 0, ',', '.') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="3" class="text-end">Total:</th>
                                            <th><strong class="text-primary">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</strong></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Aksi</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex gap-2 flex-wrap">
                                @if($order->status == 'pending')
                                    <button type="button" class="btn btn-success" onclick="updateOrderStatus('confirmed')">
                                        <i class="fas fa-check"></i> Konfirmasi Pesanan
                                    </button>
                                    <button type="button" class="btn btn-danger" onclick="updateOrderStatus('cancelled')">
                                        <i class="fas fa-times"></i> Batalkan Pesanan
                                    </button>
                                @endif
                                
                                @if($order->status == 'confirmed')
                                    <button type="button" class="btn btn-primary" onclick="updateOrderStatus('processing')">
                                        <i class="fas fa-cog"></i> Mulai Proses
                                    </button>
                                    <button type="button" class="btn btn-danger" onclick="updateOrderStatus('cancelled')">
                                        <i class="fas fa-times"></i> Batalkan Pesanan
                                    </button>
                                @endif
                                
                                @if($order->status == 'processing')
                                    <button type="button" class="btn btn-success" onclick="updateOrderStatus('ready')">
                                        <i class="fas fa-clock"></i> Siap Diambil
                                    </button>
                                @endif
                                
                                @if($order->status == 'ready')
                                    <button type="button" class="btn btn-success" onclick="updateOrderStatus('completed')">
                                        <i class="fas fa-check-double"></i> Selesaikan Pesanan
                                    </button>
                                @endif
                                
                                @if(!in_array($order->status, ['completed', 'cancelled']))
                                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addNoteModal">
                                        <i class="fas fa-sticky-note"></i> Tambah Catatan
                                    </button>
                                @endif

                                @if($order->payment_status === 'pending' && $order->payment_proof)
                                    <form method="POST" action="{{ route('admin.orders.approve-payment', $order->id) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-check-circle"></i> Approve Payment
                                        </button>
                                    </form>
                                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectPaymentModal">
                                        <i class="fas fa-times-circle"></i> Tolak Pembayaran
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Note Modal -->
<div class="modal fade" id="addNoteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Catatan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addNoteForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="note" class="form-label">Catatan</label>
                        <textarea class="form-control" id="note" name="note" rows="3" placeholder="Masukkan catatan untuk pesanan ini..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Simpan Catatan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Payment Modal -->
<div class="modal fade" id="rejectPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Alasan Penolakan Pembayaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.orders.reject-payment', $order->id) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="reject_reason" class="form-label">Alasan</label>
                        <textarea id="reject_reason" name="reason" class="form-control" rows="3" placeholder="Masukkan alasan penolakan" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Tolak Pembayaran</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
@media print {
    .btn, .card-header .btn, .modal {
        display: none !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    
    .container-fluid {
        padding: 0 !important;
    }
}
</style>
@endpush

@push('scripts')
<script>
function updateOrderStatus(status) {
    if (confirm(`Apakah Anda yakin ingin mengubah status pesanan menjadi ${status}?`)) {
        fetch(`/admin/orders/{{ $order->id }}/status`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ status: status })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Gagal memperbarui status pesanan: ' + (data.message || 'Terjadi kesalahan'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Gagal memperbarui status pesanan');
        });
    }
}

function printOrder() {
    window.print();
}

// Add note form submission
document.getElementById('addNoteForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const note = document.getElementById('note').value;
    if (!note.trim()) {
        alert('Silakan masukkan catatan');
        return;
    }
    
    fetch(`/admin/orders/{{ $order->id }}/note`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ note: note })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Gagal menambahkan catatan: ' + (data.message || 'Terjadi kesalahan'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Gagal menambahkan catatan');
    });
});
</script>
@endpush