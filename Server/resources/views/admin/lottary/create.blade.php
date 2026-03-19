@extends('backend.layouts.app')

@section('content')
<div class="card">
    <div class="card-header">
        <h4 class="mb-0">Create New Lottery</h4>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.lottary.store') }}" method="POST" enctype="multipart/form-data" id="lotteryForm" novalidate>
            @csrf
            
            <div class="row">
                <!-- Basic Information -->
                <div class="col-md-8">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Basic Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="title" class="form-label">Title *</label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                       id="title" name="title" value="{{ old('title') }}" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" name="description" rows="3">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            @php
                                $system_currency = get_system_currency();
                            @endphp
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="price" class="form-label">Price *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">{{ $system_currency->symbol }}</span>
                                        <input type="number" step="0.01" min="0"
                                               class="form-control @error('price') is-invalid @enderror"
                                               id="price" name="price" value="{{ old('price') }}" required>
                                    </div>
                                    @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            
                                {{-- Start Date & Time --}}
                                <div class="col-md-6 mb-3">
                                    <label for="start_date" class="form-label">Start Date & Time *</label>
                                
                                    <div class="input-group">
                                        <input type="datetime-local"
                                               class="form-control @error('start_date') is-invalid @enderror"
                                               id="start_date"
                                               name="start_date"
                                               value="{{ old('start_date') }}"
                                               required>
                                
                                        <button type="button" class="btn btn-outline-secondary" onclick="setNow()">
                                            Now
                                        </button>
                                    </div>
                                
                                    @error('start_date')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                            
                                {{-- Draw Date & Time --}}
                                <div class="col-md-6 mb-3">
                                    <label for="drew_date" class="form-label">Draw Date & Time *</label>
                                    <input type="datetime-local"
                                           class="form-control @error('drew_date') is-invalid @enderror"
                                           id="drew_date"
                                           name="drew_date"
                                           value="{{ old('drew_date') }}"
                                           required
                                           min="{{ now()->format('Y-m-d\TH:i') }}">
                                    @error('drew_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="prize_number" class="form-label">Total Number of Prizes *</label>
                                    <input type="number" min="1" class="form-control @error('prize_number') is-invalid @enderror" 
                                           id="prize_number" name="prize_number" value="{{ old('prize_number', 1) }}" required>
                                    @error('prize_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Total prize slots available in this lottery</small>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="winner_number" class="form-label">Number of Winners *</label>
                                    <input type="number" min="1" class="form-control @error('winner_number') is-invalid @enderror" 
                                           id="winner_number" name="winner_number" value="{{ old('winner_number', 1) }}" required>
                                    @error('winner_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">How many winners will be selected</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Prizes Information -->
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0">Define Prizes</h5>
                                <small class="text-muted" id="prizeCount">Total Prizes: <span id="currentPrizes">1</span></small>
                            </div>
                            <div>
                                <button type="button" class="btn btn-sm btn-primary me-2" id="addPrize">
                                    <i class="las la-plus"></i> Add Prize
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" id="clearPrizes">
                                    <i class="las la-trash"></i> Clear All
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> 
                                <strong>Define all prizes for this lottery.</strong> 
                                Each prize will be awarded to winners. You must define at least 1 prize.
                            </div>
                            
                            <div id="prizesContainer">
                                @php
                                    $oldPrizeNames = old('prize_name', []);
                                    $oldPrizeValues = old('prize_value', []);
                                    $oldPrizeWinnerNumbers = old('prize_winner_number', []);
                                    $oldPrizeDescriptions = old('prize_description', []);
                                    $oldPrizeDrewTypes = old('prize_drew_type', []);
                                @endphp
                                
                                @if(count($oldPrizeNames) > 0)
                                    @foreach($oldPrizeNames as $index => $prizeName)
                                    <div class="prize-card card mb-3">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-12 mb-3">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <label class="form-label">Prize Name *</label>
                                                            <input type="text" class="form-control @error('prize_name.' . $index) is-invalid @enderror" 
                                                                   name="prize_name[]" 
                                                                   value="{{ $prizeName }}" required>
                                                            @error('prize_name.' . $index)
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Prize Value *</label>
                                                            <input type="text" class="form-control @error('prize_value.' . $index) is-invalid @enderror" 
                                                                   name="prize_value[]" 
                                                                   value="{{ $oldPrizeValues[$index] ?? '' }}" required>
                                                            @error('prize_value.' . $index)
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-md-12 mb-3">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <label class="form-label">Winner Number (Optional)</label>
                                                            <input type="number" min="1" class="form-control @error('prize_winner_number.' . $index) is-invalid @enderror" 
                                                                   name="prize_winner_number[]" 
                                                                   value="{{ $oldPrizeWinnerNumbers[$index] ?? '' }}">
                                                            @error('prize_winner_number.' . $index)
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Prize Photo (Optional)</label>
                                                            <input type="file" class="form-control @error('prize_photo.' . $index) is-invalid @enderror" 
                                                                   name="prize_photo[]" accept="image/*">
                                                            @error('prize_photo.' . $index)
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- New Prize Drew Type Dropdown -->
                                                <div class="col-md-12 mb-3">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <label class="form-label">Prize Drew Type *</label>
                                                            <select class="form-control @error('prize_drew_type.' . $index) is-invalid @enderror" 
                                                                    name="prize_drew_type[]" required>
                                                                <option value="">Select Drew Type</option>
                                                                <option value="Real" {{ ($oldPrizeDrewTypes[$index] ?? '') == 'Real' ? 'selected' : '' }}>Real</option>
                                                                <option value="Fake" {{ ($oldPrizeDrewTypes[$index] ?? '') == 'Fake' ? 'selected' : '' }}>Fake</option>
                                                            </select>
                                                            @error('prize_drew_type.' . $index)
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Prize Description (Optional)</label>
                                                            <textarea class="form-control @error('prize_description.' . $index) is-invalid @enderror" 
                                                                      name="prize_description[]" rows="2">{{ $oldPrizeDescriptions[$index] ?? '' }}</textarea>
                                                            @error('prize_description.' . $index)
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-md-12 text-end">
                                                    <button type="button" class="btn btn-danger remove-prize">
                                                        <i class="las la-trash"></i> Remove Prize
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                @else
                                <!-- Initial prize row -->
                                <div class="prize-card card mb-3">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-12 mb-3">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Prize Name *</label>
                                                        <input type="text" class="form-control" name="prize_name[]" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Prize Value *</label>
                                                        <input type="text" class="form-control" name="prize_value[]" required>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-12 mb-3">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Winner Number (Optional)</label>
                                                        <input type="number" min="1" class="form-control" name="prize_winner_number[]">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Prize Photo (Optional)</label>
                                                        <input type="file" class="form-control" name="prize_photo[]" accept="image/*">
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- New Prize Drew Type Dropdown -->
                                            <div class="col-md-12 mb-3">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Prize Drew Type *</label>
                                                        <select class="form-control" name="prize_drew_type[]" required>
                                                            <option value="">Select Drew Type</option>
                                                            <option value="Real">Real</option>
                                                            <option value="Fake">Fake</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Prize Description (Optional)</label>
                                                        <textarea class="form-control" name="prize_description[]" rows="2"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-12 text-end">
                                                <button type="button" class="btn btn-danger remove-prize" disabled>
                                                    <i class="las la-trash"></i> Remove Prize
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Sidebar -->
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Featured Image</h5>
                        </div>
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <img id="imagePreview" src="https://via.placeholder.com/300x200?text=Lottery+Image" 
                                     alt="Image Preview" class="img-fluid rounded" style="max-height: 200px;">
                            </div>
                            <div class="mb-3">
                                <input type="file" class="form-control @error('photo') is-invalid @enderror" 
                                       id="photo" name="photo" accept="image/*">
                                @error('photo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="text-muted">Recommended: 300x200px, JPG, PNG or WebP format</small>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-body">
                            <button type="submit" class="btn btn-success w-100 mb-2" id="submitBtn">
                                <i class="fas fa-save"></i> Create Lottery
                            </button>
                            <a href="{{ route('admin.lottary.index') }}" class="btn btn-secondary w-100">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
    .prize-card {
        border: 1px solid #dee2e6;
        border-radius: 5px;
        transition: all 0.3s ease;
    }
    .prize-card:hover {
        border-color: #007bff;
        box-shadow: 0 0 0 1px rgba(0, 123, 255, 0.25);
    }
    .prize-card .card-body {
        padding: 15px;
    }
    #imagePreview {
        border: 2px dashed #dee2e6;
        padding: 5px;
        transition: all 0.3s ease;
    }
    .remove-prize:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    .invalid-prize {
        border-left: 4px solid #dc3545 !important;
        background: #f8d7da !important;
    }
</style>

<script>
function setNow() {
    const now = new Date();

    const year   = now.getFullYear();
    const month  = String(now.getMonth() + 1).padStart(2, '0');
    const day    = String(now.getDate()).padStart(2, '0');
    const hour   = String(now.getHours()).padStart(2, '0');
    const minute = String(now.getMinutes()).padStart(2, '0');

    document.getElementById('start_date').value =
        `${year}-${month}-${day}T${hour}:${minute}`;
}
</script>

<script>
document.getElementById('start_date').addEventListener('change', function () {
    document.getElementById('drew_date').min = this.value;
});
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Image Preview
        const photoInput = document.getElementById('photo');
        if (photoInput) {
            photoInput.addEventListener('change', function(event) {
                const reader = new FileReader();
                reader.onload = function() {
                    const output = document.getElementById('imagePreview');
                    output.src = reader.result;
                    output.style.borderColor = '#28a745';
                };
                if (event.target.files[0]) {
                    reader.readAsDataURL(event.target.files[0]);
                }
            });
        }
        
        // Update prize counter
        function updatePrizeCounter() {
            const prizeCards = document.querySelectorAll('.prize-card').length;
            document.getElementById('currentPrizes').textContent = prizeCards;
            
            // Enable/disable remove buttons
            document.querySelectorAll('.remove-prize').forEach((btn, index) => {
                btn.disabled = prizeCards <= 1;
            });
        }
        
        // Add new prize row
        document.getElementById('addPrize').addEventListener('click', function() {
            const container = document.getElementById('prizesContainer');
            
            const newCard = document.createElement('div');
            newCard.className = 'prize-card card mb-3';
            newCard.innerHTML = `
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">Prize Name *</label>
                                    <input type="text" class="form-control" name="prize_name[]" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Prize Value *</label>
                                    <input type="text" class="form-control" name="prize_value[]" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">Winner Number (Optional)</label>
                                    <input type="number" min="1" class="form-control" name="prize_winner_number[]">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Prize Photo (Optional)</label>
                                    <input type="file" class="form-control" name="prize_photo[]" accept="image/*">
                                </div>
                            </div>
                        </div>

                        <!-- New Prize Drew Type Dropdown -->
                        <div class="col-md-12 mb-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">Prize Drew Type *</label>
                                    <select class="form-control" name="prize_drew_type[]" required>
                                        <option value="">Select Drew Type</option>
                                        <option value="Real">Real</option>
                                        <option value="Fake">Fake</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Prize Description (Optional)</label>
                                    <textarea class="form-control" name="prize_description[]" rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-12 text-end">
                            <button type="button" class="btn btn-danger remove-prize">
                                <i class="las la-trash"></i> Remove Prize
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            // Add to container
            container.appendChild(newCard);
            
            // Update counter
            updatePrizeCounter();
            
            // Add animation
            newCard.style.opacity = '0';
            setTimeout(() => {
                newCard.style.transition = 'opacity 0.5s ease';
                newCard.style.opacity = '1';
            }, 10);
        });
        
        // Remove prize card (event delegation)
        document.getElementById('prizesContainer').addEventListener('click', function(e) {
            if (e.target.closest('.remove-prize')) {
                const card = e.target.closest('.prize-card');
                if (document.querySelectorAll('.prize-card').length > 1) {
                    // Add fade out animation
                    card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                    card.style.opacity = '0';
                    card.style.transform = 'translateX(-50px)';
                    
                    setTimeout(() => {
                        card.remove();
                        updatePrizeCounter();
                    }, 300);
                }
            }
        });
        
        // Clear all prizes
        document.getElementById('clearPrizes').addEventListener('click', function() {
            if (confirm('Are you sure you want to clear all prizes?')) {
                const container = document.getElementById('prizesContainer');
                const cards = container.querySelectorAll('.prize-card');
                
                // Keep only the first card
                cards.forEach((card, index) => {
                    if (index > 0) {
                        card.remove();
                    }
                });
                
                // Clear first card inputs
                const firstCard = container.querySelector('.prize-card');
                const inputs = firstCard.querySelectorAll('input, textarea, select');
                inputs.forEach(input => {
                    if (input.type !== 'file' && input.tagName !== 'SELECT') {
                        input.value = '';
                    } else if (input.tagName === 'SELECT') {
                        input.value = '';
                    }
                });
                
                updatePrizeCounter();
                
                showToast('All prizes cleared!', 'info');
            }
        });
        
        // Custom form validation
        document.getElementById('lotteryForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Clear previous validation styles
            document.querySelectorAll('.prize-card').forEach(card => {
                card.classList.remove('invalid-prize');
            });
            
            // Validate basic fields
            const requiredFields = ['title', 'price', 'drew_date', 'prize_number', 'winner_number'];
            let isValid = true;
            
            requiredFields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            // Validate prize fields
            const prizeCards = document.querySelectorAll('.prize-card');
            if (prizeCards.length < 1) {
                isValid = false;
                showToast('Please add at least one prize!', 'error');
                return false;
            }
            
            let prizeErrors = false;
            prizeCards.forEach((card) => {
                const nameInput = card.querySelector('input[name="prize_name[]"]');
                const valueInput = card.querySelector('input[name="prize_value[]"]');
                const drewTypeSelect = card.querySelector('select[name="prize_drew_type[]"]');
                
                if (!nameInput.value.trim() || !valueInput.value.trim() || !drewTypeSelect.value) {
                    card.classList.add('invalid-prize');
                    prizeErrors = true;
                    
                    // Add validation to the select if empty
                    if (!drewTypeSelect.value) {
                        drewTypeSelect.classList.add('is-invalid');
                    } else {
                        drewTypeSelect.classList.remove('is-invalid');
                    }
                } else {
                    drewTypeSelect.classList.remove('is-invalid');
                }
            });
            
            if (prizeErrors) {
                showToast('Please fill all required prize fields!', 'error');
                isValid = false;
            }
            
            // If valid, submit the form
            if (isValid) {
                // Remove novalidate attribute temporarily for server validation
                this.removeAttribute('novalidate');
                
                // Disable submit button to prevent double submission
                const submitBtn = document.getElementById('submitBtn');
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';
                
                // Submit the form
                this.submit();
            } else {
                // Scroll to first error
                const firstError = document.querySelector('.is-invalid, .invalid-prize');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
        
        // Real-time validation for prize fields
        document.getElementById('prizesContainer').addEventListener('input', function(e) {
            if (e.target.matches('input[name="prize_name[]"], input[name="prize_value[]"]')) {
                const card = e.target.closest('.prize-card');
                const nameInput = card.querySelector('input[name="prize_name[]"]');
                const valueInput = card.querySelector('input[name="prize_value[]"]');
                const drewTypeSelect = card.querySelector('select[name="prize_drew_type[]"]');
                
                if (nameInput.value.trim() && valueInput.value.trim() && drewTypeSelect.value) {
                    card.classList.remove('invalid-prize');
                    drewTypeSelect.classList.remove('is-invalid');
                }
            }
        });
        
        // Real-time validation for drew type select
        document.getElementById('prizesContainer').addEventListener('change', function(e) {
            if (e.target.matches('select[name="prize_drew_type[]"]')) {
                const card = e.target.closest('.prize-card');
                const nameInput = card.querySelector('input[name="prize_name[]"]');
                const valueInput = card.querySelector('input[name="prize_value[]"]');
                const drewTypeSelect = e.target;
                
                if (drewTypeSelect.value) {
                    drewTypeSelect.classList.remove('is-invalid');
                }
                
                if (nameInput.value.trim() && valueInput.value.trim() && drewTypeSelect.value) {
                    card.classList.remove('invalid-prize');
                }
            }
        });
        
        // Helper function for toast notifications
        function showToast(message, type = 'info') {
            // Check if Bootstrap toast is available
            if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
                // Create toast element
                const toastContainer = document.getElementById('toastContainer') || createToastContainer();
                const toastId = 'toast-' + Date.now();
                
                const toastHtml = `
                    <div id="${toastId}" class="toast align-items-center text-white bg-${type === 'error' ? 'danger' : type === 'success' ? 'success' : 'info'} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="d-flex">
                            <div class="toast-body">
                                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
                                ${message}
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                    </div>
                `;
                
                toastContainer.insertAdjacentHTML('beforeend', toastHtml);
                
                const toastElement = document.getElementById(toastId);
                const toast = new bootstrap.Toast(toastElement, {
                    delay: 3000
                });
                
                toast.show();
                
                // Remove toast after it's hidden
                toastElement.addEventListener('hidden.bs.toast', function() {
                    toastElement.remove();
                });
            } else {
                // Fallback alert
                alert(message);
            }
        }
        
        function createToastContainer() {
            const container = document.createElement('div');
            container.id = 'toastContainer';
            container.className = 'toast-container position-fixed top-0 end-0 p-3';
            container.style.zIndex = '9999';
            document.body.appendChild(container);
            return container;
        }
        
        // Initialize prize counter
        updatePrizeCounter();
    });
</script>
@endsection