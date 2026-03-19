@extends('backend.layouts.app')

@section('content')
<div class="card">
    <div class="card-header">
        <h4 class="mb-0">Edit Lottery</h4>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.lottary.update', $lottary->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
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
                                       id="title" name="title" value="{{ old('title', $lottary->title) }}" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" name="description" rows="3">{{ old('description', $lottary->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            @php
                                $system_currency = get_system_currency();
                            @endphp
                            <div class="row">
                                {{-- Price --}}
                                <div class="col-md-6 mb-3">
                                    <label for="price" class="form-label">Price *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">{{ $system_currency->symbol }}</span>
                                        <input type="number" step="0.01"
                                               class="form-control @error('price') is-invalid @enderror"
                                               id="price" name="price"
                                               value="{{ old('price', $lottary->price) }}" required>
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
                                               value="{{ old('start_date', \Carbon\Carbon::parse($lottary->start_date)->format('Y-m-d\TH:i')) }}"
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
                                           value="{{ old('drew_date', \Carbon\Carbon::parse($lottary->drew_date)->format('Y-m-d\TH:i')) }}"
                                           required>
                                    @error('drew_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                {{-- Active Status --}}
                                <div class="col-md-6 mb-3">
                                    <label for="is_active" class="form-label">Status *</label>
                                    <select name="is_active" id="is_active"
                                            style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; background-color: #f8f9fa; color: #212529; font-size: 1rem; line-height: 1.5;"
                                            required>
                                        <option value="1" {{ old('is_active', $lottary->is_active) == 1 ? 'selected' : '' }}>
                                            Active
                                        </option>
                                        <option value="0" {{ old('is_active', $lottary->is_active) == 0 ? 'selected' : '' }}>
                                            Inactive
                                        </option>
                                    </select>
                                
                                    @error('is_active')
                                        <div style="color: #dc3545; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="prize_number" class="form-label">Total Number of Prizes *</label>
                                    <input type="number" min="1" class="form-control @error('prize_number') is-invalid @enderror" 
                                           id="prize_number" name="prize_number" 
                                           value="{{ old('prize_number', $lottary->prize_number) }}" required>
                                    @error('prize_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="winner_number" class="form-label">Number of Winners *</label>
                                    <input type="number" min="1" class="form-control @error('winner_number') is-invalid @enderror" 
                                           id="winner_number" name="winner_number" 
                                           value="{{ old('winner_number', $lottary->winner_number) }}" required>
                                    @error('winner_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Prizes Information -->
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Prizes Information</h5>
                            <button type="button" class="btn btn-sm btn-primary" id="addPrize">
                                <i class="fas fa-plus"></i> Add Prize
                            </button>
                        </div>
                        <div class="card-body">
                            <div id="prizesContainer">
                                @php
                                    $prizes = $lottary->prizes;
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
                                    @forelse($prizes as $index => $prize)
                                    <div class="prize-card card mb-3">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-12 mb-3">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <label class="form-label">Prize Name *</label>
                                                            <input type="text" class="form-control" 
                                                                   name="prize_name[]" 
                                                                   value="{{ old('prize_name.' . $index, $prize->name) }}" required>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Prize Value *</label>
                                                            <input type="text" class="form-control" 
                                                                   name="prize_value[]" 
                                                                   value="{{ old('prize_value.' . $index, $prize->prize_value) }}" required>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-md-12 mb-3">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <label class="form-label">Winner Number (Optional)</label>
                                                            <input type="number" min="1" class="form-control" 
                                                                   name="prize_winner_number[]" 
                                                                   value="{{ old('prize_winner_number.' . $index, $prize->winner_number) }}">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Prize Photo (Optional)</label>
                                                            <input type="file" class="form-control" 
                                                                   name="prize_photo[]" accept="image/*">
                                                            @if($prize->photo)
                                                                <div class="mt-2">
                                                                    <img src="{{ asset($prize->photo) }}" 
                                                                         alt="Prize Photo" 
                                                                         class="img-thumbnail" 
                                                                         style="max-height: 80px;">
                                                                    <small class="text-muted">Current photo</small>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-md-12 mb-3">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <label class="form-label">Prize Drew Type *</label>
                                                            <select class="form-control" name="prize_drew_type[]" required>
                                                                <option value="">Select Drew Type</option>
                                                                <option value="Real" {{ old('prize_drew_type.' . $index, $prize->drew_type) == 'Real' ? 'selected' : '' }}>Real</option>
                                                                <option value="Fake" {{ old('prize_drew_type.' . $index, $prize->drew_type) == 'Fake' ? 'selected' : '' }}>Fake</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Prize Description (Optional)</label>
                                                            <textarea class="form-control" 
                                                                      name="prize_description[]" rows="2">{{ old('prize_description.' . $index, $prize->description) }}</textarea>
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
                                    @empty
                                    <div class="prize-card card mb-3">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-12 mb-3">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <label class="form-label">Prize Name *</label>
                                                            <input type="text" class="form-control" 
                                                                   name="prize_name[]" required>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Prize Value *</label>
                                                            <input type="text" class="form-control" 
                                                                   name="prize_value[]" required>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-md-12 mb-3">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <label class="form-label">Winner Number (Optional)</label>
                                                            <input type="number" min="1" class="form-control" 
                                                                   name="prize_winner_number[]">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Prize Photo (Optional)</label>
                                                            <input type="file" class="form-control" 
                                                                   name="prize_photo[]" accept="image/*">
                                                        </div>
                                                    </div>
                                                </div>
                                                
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
                                                            <textarea class="form-control" 
                                                                      name="prize_description[]" rows="2"></textarea>
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
                                    @endforelse
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
                                @if($lottary->photo)
                                    <img id="imagePreview" src="{{ asset($lottary->photo) }}" 
                                         alt="Current Image" 
                                         class="img-fluid rounded" 
                                         style="max-height: 200px;">
                                @else
                                    <img id="imagePreview" src="https://via.placeholder.com/300x200?text=Lottery+Image" 
                                         alt="Image Preview" 
                                         class="img-fluid rounded" 
                                         style="max-height: 200px;">
                                @endif
                            </div>

                            <div class="mb-3">
                                <input type="file" class="form-control @error('photo') is-invalid @enderror" 
                                       id="photo" name="photo" accept="image/*" onchange="previewImage(event)">
                                @error('photo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            @if($lottary->photo)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="remove_photo" name="remove_photo">
                                    <label class="form-check-label text-danger" for="remove_photo">
                                        Remove current image
                                    </label>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Status</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Current Status</label>
                                <div>
                                    @if($lottary->is_active)
                                        <span style="display:inline-block;padding:3px 10px;border-radius:12px;font-size:12px;font-weight:600;background:linear-gradient(135deg,#198754,#20c997);color:white;box-shadow:0 2px 4px rgba(25,135,84,0.2);transition:all 0.3s ease;" onmouseover="this.style.transform='translateY(-1px)';this.style.boxShadow='0 4px 8px rgba(25,135,84,0.3)'" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 2px 4px rgba(25,135,84,0.2)'">Active</span>
                                    @else
                                        <span style="display:inline-block;padding:3px 10px;border-radius:12px;font-size:12px;font-weight:600;background:linear-gradient(135deg,#dc3545,#e83e8c);color:white;box-shadow:0 2px 4px rgba(220,53,69,0.2);transition:all 0.3s ease;" onmouseover="this.style.transform='translateY(-1px)';this.style.boxShadow='0 4px 8px rgba(220,53,69,0.3)'" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 2px 4px rgba(220,53,69,0.2)'">Inactive</span>
                                    @endif
                                    @if($lottary->is_drew)
                                        <span style="display:inline-block;padding:3px 10px;border-radius:12px;font-size:12px;font-weight:600;background:linear-gradient(135deg,#198754,#20c997);color:white;box-shadow:0 2px 4px rgba(25,135,84,0.2);transition:all 0.3s ease;" onmouseover="this.style.transform='translateY(-1px)';this.style.boxShadow='0 4px 8px rgba(25,135,84,0.3)'" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 2px 4px rgba(25,135,84,0.2)'">Drawn</span>
                                    @else
                                        <span style="display:inline-block;padding:3px 10px;border-radius:12px;font-size:12px;font-weight:600;background:linear-gradient(135deg,#ffc107,#fd7e14);color:#000;box-shadow:0 2px 4px rgba(255,193,7,0.2);transition:all 0.3s ease;" onmouseover="this.style.transform='translateY(-1px)';this.style.boxShadow='0 4px 8px rgba(255,193,7,0.3)'" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 2px 4px rgba(255,193,7,0.2)'">Pending</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-body">
                            <button type="submit" class="btn btn-success w-100 mb-2">
                                <i class="fas fa-save"></i> Update Lottery
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
    }
    .prize-card .card-body {
        padding: 15px;
    }
    #imagePreview {
        border: 2px dashed #dee2e6;
        padding: 5px;
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

    const value = `${year}-${month}-${day}T${hour}:${minute}`;

    document.getElementById('start_date').value = value;
    document.getElementById('drew_date').min   = value;
}
</script>


<script>
    // Image Preview for main image
    function previewImage(event) {
        const reader = new FileReader();
        reader.onload = function() {
            const output = document.getElementById('imagePreview');
            output.src = reader.result;
        };
        reader.readAsDataURL(event.target.files[0]);
    }
    
    // Remove photo checkbox handler
    document.getElementById('remove_photo')?.addEventListener('change', function() {
        const preview = document.getElementById('imagePreview');
        if (this.checked) {
            preview.src = 'https://via.placeholder.com/300x200?text=Image+Removed';
            document.getElementById('photo').value = '';
        } else {
            preview.src = '{{ $lottary->photo ? asset($lottary->photo) : "https://via.placeholder.com/300x200?text=Lottery+Image" }}';
        }
    });
    
    // Add Prize Row
    document.getElementById('addPrize').addEventListener('click', function() {
        const container = document.getElementById('prizesContainer');
        const index = container.querySelectorAll('.prize-card').length;
        
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
        
        container.appendChild(newCard);
        
        // Enable all remove buttons
        document.querySelectorAll('.remove-prize').forEach(btn => {
            btn.disabled = false;
            btn.addEventListener('click', function() {
                if (container.querySelectorAll('.prize-card').length > 1) {
                    this.closest('.prize-card').remove();
                }
            });
        });
    });
    
    // Initialize remove buttons
    document.querySelectorAll('.remove-prize').forEach(btn => {
        btn.addEventListener('click', function() {
            const container = document.getElementById('prizesContainer');
            if (container.querySelectorAll('.prize-card').length > 1) {
                this.closest('.prize-card').remove();
            }
        });
    });
</script>
@endsection