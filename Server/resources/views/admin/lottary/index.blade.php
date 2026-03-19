@extends('backend.layouts.app')

@section('content')
<div style="background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 30px;">
    <div style="padding: 20px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;">
        <h4 style="margin: 0; color: #333; font-weight: 600;">Lottery Management</h4>
        <a href="{{ route('admin.lottary.create') }}" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: 500; display: flex; align-items: center; gap: 8px; transition: all 0.3s;">
            <svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16" style="margin-top: -2px;">
                <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
            </svg>
            Add New Lottery
        </a>
    </div>
    
    <div style="padding: 20px;">
        @if(session('success'))
            <div style="background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%); border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 6px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                    </svg>
                    {{ session('success') }}
                </div>
                <button type="button" onclick="this.parentElement.style.display='none'" style="background: none; border: none; color: #155724; cursor: pointer; padding: 0; font-size: 18px;">
                    ×
                </button>
            </div>
        @endif

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                        <th style="padding: 15px 12px; text-align: left; border-bottom: 2px solid #dee2e6; color: #495057; font-weight: 600;">#</th>
                        <th style="padding: 15px 12px; text-align: left; border-bottom: 2px solid #dee2e6; color: #495057; font-weight: 600;">Title</th>
                        <th style="padding: 15px 12px; text-align: left; border-bottom: 2px solid #dee2e6; color: #495057; font-weight: 600;">Photo</th>
                        <th style="padding: 15px 12px; text-align: left; border-bottom: 2px solid #dee2e6; color: #495057; font-weight: 600;">Price</th>
                        <th style="padding: 15px 12px; text-align: left; border-bottom: 2px solid #dee2e6; color: #495057; font-weight: 600;">Total Prizes</th>
                        <th style="padding: 15px 12px; text-align: left; border-bottom: 2px solid #dee2e6; color: #495057; font-weight: 600;">Winners</th>
                        <th style="padding: 15px 12px; text-align: left; border-bottom: 2px solid #dee2e6; color: #495057; font-weight: 600;">Start Date</th>
                        <th style="padding: 15px 12px; text-align: left; border-bottom: 2px solid #dee2e6; color: #495057; font-weight: 600;">Draw Date</th>
                        <th style="padding: 15px 12px; text-align: left; border-bottom: 2px solid #dee2e6; color: #495057; font-weight: 600;">Status</th>
                        <th style="padding: 15px 12px; text-align: left; border-bottom: 2px solid #dee2e6; color: #495057; font-weight: 600;">Draw Status</th>
                        <th style="padding: 15px 12px; text-align: left; border-bottom: 2px solid #dee2e6; color: #495057; font-weight: 600;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lottaries as $lottary)
                    <tr style="border-bottom: 1px solid #eee; transition: background 0.3s;" onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background='transparent'">
                        <td style="padding: 15px 12px; color: #666;">{{ ($lottaries->currentPage() - 1) * $lottaries->perPage() + $loop->iteration }}</td>
                        <td style="padding: 15px 12px; font-weight: 500; color: #333;">{{ $lottary->title }}</td>
                        <td style="padding: 15px 12px;">
                            @if($lottary->photo)
                                <img src="{{ url($lottary->photo) }}" 
                                     alt="{{ $lottary->title }}" 
                                     style="width:60px; height:60px; object-fit:cover; border-radius:6px; border:1px solid #dee2e6;">
                            @else
                                <span style="color: #999; font-style: italic;">No Image</span>
                            @endif
                        </td>
                        @php
                            $system_currency = get_system_currency();
                        @endphp
                        <td style="padding: 15px 12px; font-weight: 600; color: #28a745;">{{ $system_currency->symbol }} {{ number_format($lottary->price, 2) }}</td>
                        <td style="padding: 15px 12px;">
                            <button type="button" class="view-prizes-btn" 
                                    style="background: none; border: none; color: #17a2b8; padding: 0; cursor: pointer; font-weight: 500; display: flex; align-items: center; gap: 5px;"
                                    data-lottary-id="{{ $lottary->id }}"
                                    data-lottary-title="{{ $lottary->title }}"
                                    data-prizes-url="{{ route('admin.lottary.prizes', $lottary->id) }}">
                                <span style="background: white; color: #495057; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 600; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">{{ $lottary->prize_number }}</span>
                                <span style="color: #6c757d; font-size: 12px;">({{ $lottary->prizes_count }} defined)</span>
                            </button>
                        </td>
                        <td style="padding: 15px 12px;">
                            <button type="button" class="view-winners-btn" 
                                    style="background: none; border: none; color: #28a745; padding: 0; cursor: pointer; font-weight: 500; display: flex; align-items: center; gap: 5px;"
                                    data-lottary-id="{{ $lottary->id }}"
                                    data-lottary-title="{{ $lottary->title }}"
                                    data-winners-url="{{ route('user.lottary.winners', $lottary->id) }}">
                                <span style="background: white; color: #495057; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 600; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">{{ $lottary->winner_number }}</span>
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                    <path d="M8 13A5 5 0 1 0 8 3a5 5 0 0 0 0 10zm0-2A3 3 0 1 1 8 5a3 3 0 0 1 0 6z"/>
                                </svg>
                            </button>
                        </td>
                        <td style="padding: 15px 12px; color: #666;">
                            {{ \Carbon\Carbon::parse($lottary->start_date)->format('M d, Y h:i A') }}
                        </td>
                        <td style="padding: 15px 12px; color: #666;">
                            {{ \Carbon\Carbon::parse($lottary->drew_date)->format('M d, Y h:i A') }}
                        </td>

                        <td style="padding: 15px 12px;">
                            @if($lottary->is_active)
                                <span style="display:inline-block; padding:5px 12px; border-radius:20px; font-size:12px; font-weight:600; background:linear-gradient(135deg,#28a745,#20c997); color:white; box-shadow:0 2px 4px rgba(40,167,69,0.2);">Active</span>
                            @else
                                <span style="display:inline-block; padding:5px 12px; border-radius:20px; font-size:12px; font-weight:600; background:linear-gradient(135deg,#dc3545,#e83e8c); color:white; box-shadow:0 2px 4px rgba(220,53,69,0.2);">Inactive</span>
                            @endif
                        </td>
                        <td style="padding: 15px 12px;">
                            @if($lottary->is_drew)
                                <span style="display:inline-block; padding:5px 12px; border-radius:20px; font-size:12px; font-weight:600; background:linear-gradient(135deg,#28a745,#20c997); color:white; box-shadow:0 2px 4px rgba(40,167,69,0.2);">Drawn</span>
                            @else
                                <span style="display:inline-block; padding:5px 12px; border-radius:20px; font-size:12px; font-weight:600; background:linear-gradient(135deg,#ffc107,#fd7e14); color:#212529; box-shadow:0 2px 4px rgba(255,193,7,0.2);">Pending</span>
                            @endif
                        </td>
                        <td style="padding: 15px 12px;">
                            <div style="display: flex; gap: 5px;">
                                <a href="{{ route('admin.lottary.tickets', $lottary->id) }}" 
                                   style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); color: white; width: 32px; height: 32px; border-radius: 6px; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: all 0.3s;"
                                   onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(23,162,184,0.3)'"
                                   onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'"
                                   title="View Tickets">
                                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M8 3a5 5 0 0 0-5 5 5 5 0 0 0 10 0 5 5 0 0 0-5-5zM1 8a7 7 0 1 1 14 0A7 7 0 0 1 1 8z"/>
                                        <path d="M8 5a3 3 0 1 0 0 6 3 3 0 0 0 0-6z"/>
                                    </svg>
                                </a>
                                <a href="{{ route('admin.lottary.edit', $lottary->id) }}" 
                                   style="background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%); color: white; width: 32px; height: 32px; border-radius: 6px; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: all 0.3s;"
                                   onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(255,193,7,0.3)'"
                                   onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207 11.207 2.5zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293l6.5-6.5zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325z"/>
                                    </svg>
                                </a>
                                <a href="{{ route('admin.lottary.delete', $lottary->id ) }}" 
                                   onclick="return confirm('Are you sure you want to delete this lottery?')"
                                   style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; width: 32px; height: 32px; border-radius: 6px; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: all 0.3s;"
                                   onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(220,53,69,0.3)'"
                                   onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                                        <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" style="padding: 50px 20px; text-align: center; color: #6c757d;">
                            <svg width="64" height="64" fill="currentColor" viewBox="0 0 16 16" style="margin-bottom: 20px; opacity: 0.5;">
                                <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1H2zm13 2.383-4.708 2.825L15 11.105V5.383zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741zM1 11.105l4.708-2.897L1 5.383v5.722z"/>
                            </svg>
                            <h4 style="margin: 0 0 10px 0; color: #6c757d;">No lotteries found</h4>
                            <p style="margin: 0; color: #adb5bd;">Create your first lottery to get started</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($lottaries->hasPages())
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
            <div style="color: #6c757d; font-size: 14px;">
                Showing {{ $lottaries->firstItem() ?? 0 }} to {{ $lottaries->lastItem() ?? 0 }} of {{ $lottaries->total() }} entries
            </div>
            <nav>
                <ul style="display: flex; list-style: none; margin: 0; padding: 0; gap: 5px;">
                    {{-- Previous Page Link --}}
                    @if($lottaries->onFirstPage())
                        <li>
                            <span style="display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 6px; background: #f8f9fa; color: #adb5bd; cursor: not-allowed;">
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                                </svg>
                            </span>
                        </li>
                    @else
                        <li>
                            <a href="{{ $lottaries->previousPageUrl() }}" 
                               style="display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 6px; background: white; color: #495057; text-decoration: none; border: 1px solid #dee2e6; transition: all 0.3s;"
                               onmouseover="this.style.background='#f8f9fa'; this.style.borderColor='#dee2e6'"
                               onmouseout="this.style.background='white'; this.style.borderColor='#dee2e6'">
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                                </svg>
                            </a>
                        </li>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach($lottaries->links()->elements[0] as $page => $url)
                        @if($page == $lottaries->currentPage())
                            <li>
                                <span style="display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 6px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; font-weight: 600;">
                                    {{ $page }}
                                </span>
                            </li>
                        @elseif(is_string($page))
                            <li>
                                <span style="display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 6px; background: #f8f9fa; color: #adb5bd;">
                                    {{ $page }}
                                </span>
                            </li>
                        @else
                            <li>
                                <a href="{{ $url }}" 
                                   style="display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 6px; background: white; color: #495057; text-decoration: none; border: 1px solid #dee2e6; transition: all 0.3s;"
                                   onmouseover="this.style.background='#f8f9fa'; this.style.borderColor='#dee2e6'"
                                   onmouseout="this.style.background='white'; this.style.borderColor='#dee2e6'">
                                    {{ $page }}
                                </a>
                            </li>
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if($lottaries->hasMorePages())
                        <li>
                            <a href="{{ $lottaries->nextPageUrl() }}" 
                               style="display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 6px; background: white; color: #495057; text-decoration: none; border: 1px solid #dee2e6; transition: all 0.3s;"
                               onmouseover="this.style.background='#f8f9fa'; this.style.borderColor='#dee2e6'"
                               onmouseout="this.style.background='white'; this.style.borderColor='#dee2e6'">
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
                                </svg>
                            </a>
                        </li>
                    @else
                        <li>
                            <span style="display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 6px; background: #f8f9fa; color: #adb5bd; cursor: not-allowed;">
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
                                </svg>
                            </span>
                        </li>
                    @endif
                </ul>
            </nav>
        </div>
        @endif
    </div>
</div>

<!-- Prize Modal -->
<div id="prizeModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1050; align-items: center; justify-content: center;">
    <div style="background: white; width: 90%; max-width: 1200px; max-height: 90vh; border-radius: 12px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); overflow: hidden; display: flex; flex-direction: column;">
        <!-- Modal Header -->
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px 30px; color: white; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <svg width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1H2zm13 2.383-4.708 2.825L15 11.105V5.383zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741zM1 11.105l4.708-2.897L1 5.383v5.722z"/>
                </svg>
                <h3 style="margin: 0; font-weight: 600; font-size: 20px;" id="modalLotteryTitle">Lottery Prizes</h3>
            </div>
            <button type="button" onclick="closePrizeModal()" style="background: rgba(255,255,255,0.2); border: none; width: 36px; height: 36px; border-radius: 50%; color: white; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 20px; transition: all 0.3s;"
                    onmouseover="this.style.background='rgba(255,255,255,0.3)'"
                    onmouseout="this.style.background='rgba(255,255,255,0.2)'">
                ×
            </button>
        </div>
        
        <!-- Modal Body -->
        <div style="padding: 0; flex: 1; overflow-y: auto;">
            <!-- Loading Spinner -->
            <div id="prizeLoadingSpinner" style="padding: 60px 20px; text-align: center;">
                <div style="width: 50px; height: 50px; border: 4px solid #f3f3f3; border-top: 4px solid #667eea; border-radius: 50%; margin: 0 auto 20px; animation: spin 1s linear infinite;"></div>
                <p style="margin: 0; color: #6c757d; font-size: 16px;">Loading prizes...</p>
            </div>
            
            <!-- Prizes Content -->
            <div id="prizesContent" style="display: none;">
                <div style="padding: 20px 30px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <svg width="20" height="20" fill="#667eea" viewBox="0 0 16 16">
                                <path d="M9.828 3h3.982a2 2 0 0 1 1.992 2.181l-.637 7A2 2 0 0 1 13.174 14H2.825a2 2 0 0 1-1.991-1.819l-.637-7a1.99 1.99 0 0 1 .342-1.31L.5 3a2 2 0 0 1 2-2h3.672a2 2 0 0 1 1.414.586l.828.828A2 2 0 0 0 9.828 3zm-8.322.12C1.72 3.042 1.95 3 2.19 3h5.396l-.707-.707A1 1 0 0 0 6.172 2H2.5a1 1 0 0 0-1 .981l.006.139z"/>
                            </svg>
                            <span style="font-weight: 600; color: #495057;">Prize Details</span>
                        </div>
                        <div id="totalPrizesCount" style="background: white; color: #667eea; padding: 8px 16px; border-radius: 20px; font-weight: 600; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                            0 prizes
                        </div>
                    </div>
                </div>
                
                <div style="padding: 20px 30px;">
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                                    <th style="padding: 15px; text-align: center; border-bottom: 2px solid #dee2e6; color: #495057; font-weight: 600; min-width: 60px;">#</th>
                                    <th style="padding: 15px; text-align: left; border-bottom: 2px solid #dee2e6; color: #495057; font-weight: 600; min-width: 200px;">Prize Name</th>
                                    <th style="padding: 15px; text-align: left; border-bottom: 2px solid #dee2e6; color: #495057; font-weight: 600; min-width: 150px;">Prize Value</th>
                                    <th style="padding: 15px; text-align: center; border-bottom: 2px solid #dee2e6; color: #495057; font-weight: 600; min-width: 120px;">Winner Number</th>
                                    <th style="padding: 15px; text-align: left; border-bottom: 2px solid #dee2e6; color: #495057; font-weight: 600; min-width: 150px;">Draw Type</th>
                                    <th style="padding: 15px; text-align: left; border-bottom: 2px solid #dee2e6; color: #495057; font-weight: 600; min-width: 250px;">Description</th>
                                    <th style="padding: 15px; text-align: center; border-bottom: 2px solid #dee2e6; color: #495057; font-weight: 600; min-width: 120px;">Photo</th>
                                </tr>
                            </thead>
                            <tbody id="prizesTableBody">
                                <!-- Prizes will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div id="noPrizesMessage" style="display: none; padding: 60px 20px; text-align: center;">
                    <svg width="80" height="80" fill="#adb5bd" viewBox="0 0 16 16" style="margin-bottom: 20px;">
                        <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1H2zm13 2.383-4.708 2.825L15 11.105V5.383zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741zM1 11.105l4.708-2.897L1 5.383v5.722z"/>
                    </svg>
                    <h4 style="margin: 0 0 10px 0; color: #6c757d;">No prizes defined</h4>
                    <p style="margin: 0; color: #adb5bd;">Add prizes by editing this lottery</p>
                </div>
            </div>
        </div>
        
        <!-- Modal Footer -->
        <div style="padding: 20px 30px; border-top: 1px solid #eee; text-align: right;">
            <button type="button" onclick="closePrizeModal()" 
                    style="background: linear-gradient(135deg, #6c757d 0%, #495057 100%); color: white; border: none; padding: 10px 24px; border-radius: 6px; font-weight: 500; cursor: pointer; transition: all 0.3s;"
                    onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(108,117,125,0.3)'"
                    onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                Close
            </button>
        </div>
    </div>
</div>

<!-- Winners Modal -->
<div id="winnersModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1051; align-items: center; justify-content: center;">
    <div style="background: white; width: 90%; max-width: 1400px; max-height: 90vh; border-radius: 12px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); overflow: hidden; display: flex; flex-direction: column;">
        <!-- Modal Header -->
        <div style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); padding: 20px 30px; color: white; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <svg width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1H2zm13 2.383-4.708 2.825L15 11.105V5.383zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741zM1 11.105l4.708-2.897L1 5.383v5.722z"/>
                </svg>
                <div>
                    <h3 style="margin: 0; font-weight: 600; font-size: 20px;" id="modalWinnersTitle">Lottery Winners</h3>
                    <div style="display: flex; align-items: center; gap: 10px; margin-top: 5px;">
                        <span id="totalWinnersCount" style="background: rgba(255,255,255,0.2); padding: 4px 12px; border-radius: 20px; font-size: 14px; font-weight: 500;">
                            0 winners
                        </span>
                        <span id="exportBtnContainer">
                            <!-- Export button will be added here -->
                        </span>
                    </div>
                </div>
            </div>
            <button type="button" onclick="closeWinnersModal()" style="background: rgba(255,255,255,0.2); border: none; width: 36px; height: 36px; border-radius: 50%; color: white; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 20px; transition: all 0.3s;"
                    onmouseover="this.style.background='rgba(255,255,255,0.3)'"
                    onmouseout="this.style.background='rgba(255,255,255,0.2)'">
                ×
            </button>
        </div>
        
        <!-- Modal Body -->
        <div style="padding: 0; flex: 1; overflow-y: auto;">
            <!-- Loading Spinner -->
            <div id="winnersLoadingSpinner" style="padding: 60px 20px; text-align: center;">
                <div style="width: 50px; height: 50px; border: 4px solid #f3f3f3; border-top: 4px solid #28a745; border-radius: 50%; margin: 0 auto 20px; animation: spin 1s linear infinite;"></div>
                <p style="margin: 0; color: #6c757d; font-size: 16px;">Loading winners...</p>
            </div>
            
            <!-- Winners Content -->
            <div id="winnersContent" style="display: none;">
                <!-- Filters and Summary -->
                <div style="padding: 20px 30px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-bottom: 1px solid #dee2e6;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <div style="display: flex; align-items: center; gap: 8px; background: white; padding: 8px 16px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                <svg width="18" height="18" fill="#28a745" viewBox="0 0 16 16">
                                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                    <path d="M8 13A5 5 0 1 0 8 3a5 5 0 0 0 0 10z"/>
                                </svg>
                                <span style="font-weight: 600; color: #495057;" id="totalWinnersSummary">Total Winners: 0</span>
                            </div>
                            <div id="userTypeFilter" style="display: flex; gap: 10px; align-items: center;">
                                <!-- Filter buttons will be added here -->
                            </div>
                        </div>
                        <div id="activeFilters" style="display: flex; align-items: center; gap: 10px;">
                            <!-- Active filters will be shown here -->
                        </div>
                    </div>
                    
                    <!-- Statistics -->
                    <div style="display: flex; gap: 15px; flex-wrap: wrap;" id="winnersStats">
                        <!-- Statistics will be loaded here -->
                    </div>
                </div>
                
                <!-- Winners Table -->
                <div style="padding: 20px 30px;">
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                                    <th style="padding: 15px; text-align: center; border-bottom: 2px solid #dee2e6; color: #495057; font-weight: 600; min-width: 60px;">#</th>
                                    <th style="padding: 15px; text-align: left; border-bottom: 2px solid #dee2e6; color: #495057; font-weight: 600; min-width: 180px;">Winner</th>
                                    <th style="padding: 15px; text-align: left; border-bottom: 2px solid #dee2e6; color: #495057; font-weight: 600; min-width: 120px;">User Type</th>
                                    <th style="padding: 15px; text-align: left; border-bottom: 2px solid #dee2e6; color: #495057; font-weight: 600; min-width: 150px;">Location</th>
                                    <th style="padding: 15px; text-align: left; border-bottom: 2px solid #dee2e6; color: #495057; font-weight: 600; min-width: 150px;">Ticket Number</th>
                                    <th style="padding: 15px; text-align: left; border-bottom: 2px solid #dee2e6; color: #495057; font-weight: 600; min-width: 200px;">Prize</th>
                                    <th style="padding: 15px; text-align: center; border-bottom: 2px solid #dee2e6; color: #495057; font-weight: 600; min-width: 120px;">Prize Photo</th>
                                    <th style="padding: 15px; text-align: center; border-bottom: 2px solid #dee2e6; color: #495057; font-weight: 600; min-width: 100px;">Winner #</th>
                                </tr>
                            </thead>
                            <tbody id="winnersTableBody">
                                <!-- Winners will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div id="noWinnersMessage" style="display: none; padding: 60px 20px; text-align: center;">
                    <svg width="80" height="80" fill="#adb5bd" viewBox="0 0 16 16" style="margin-bottom: 20px;">
                        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                        <path d="M8 13A5 5 0 1 0 8 3a5 5 0 0 0 0 10z"/>
                    </svg>
                    <h4 style="margin: 0 0 10px 0; color: #6c757d;">No winners found</h4>
                    <p style="margin: 0; color: #adb5bd;">This lottery hasn't been drawn yet or no winners have been selected</p>
                </div>
            </div>
        </div>
        
        <!-- Modal Footer -->
        <div style="padding: 20px 30px; border-top: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;">
            <div style="color: #6c757d; font-size: 14px;" id="winnersFooterInfo">
                Showing 0 entries
            </div>
            <div style="display: flex; gap: 10px;">
                <button type="button" onclick="exportWinnersToCSV()" 
                        style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); color: white; border: none; padding: 10px 24px; border-radius: 6px; font-weight: 500; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; gap: 8px;"
                        onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(23,162,184,0.3)'"
                        onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                        <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                    </svg>
                    Export CSV
                </button>
                <button type="button" onclick="closeWinnersModal()" 
                        style="background: linear-gradient(135deg, #6c757d 0%, #495057 100%); color: white; border: none; padding: 10px 24px; border-radius: 6px; font-weight: 500; cursor: pointer; transition: all 0.3s;"
                        onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(108,117,125,0.3)'"
                        onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .filter-btn {
        background: white;
        border: 2px solid #dee2e6;
        color: #6c757d;
        padding: 6px 16px;
        border-radius: 20px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s;
        font-size: 14px;
    }
    
    .filter-btn:hover {
        background: #f8f9fa;
        border-color: #adb5bd;
    }
    
    .filter-btn.active {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        border-color: transparent;
        color: white;
    }
    
    .stat-card {
        background: white;
        border-radius: 10px;
        padding: 15px 20px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        min-width: 180px;
        flex: 1;
    }
    
    .stat-card .stat-value {
        font-size: 24px;
        font-weight: 700;
        margin-bottom: 5px;
    }
    
    .stat-card .stat-label {
        font-size: 12px;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .user-type-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 12px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .user-type-real {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        color: white;
    }
    
    .user-type-fake {
        background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
        color: white;
    }
</style>

<script>
// Global variables for winners data
let currentWinnersData = [];
let currentLotteryId = null;
let currentLotteryTitle = '';
let currentFilter = 'all';

// Modal functions for prizes
function showPrizeModal() {
    document.getElementById('prizeModal').style.display = 'flex';
}

function closePrizeModal() {
    document.getElementById('prizeModal').style.display = 'none';
    resetPrizeModalContent();
}

function resetPrizeModalContent() {
    document.getElementById('prizesContent').style.display = 'none';
    document.getElementById('prizeLoadingSpinner').style.display = 'block';
    document.getElementById('noPrizesMessage').style.display = 'none';
    document.getElementById('prizesTableBody').innerHTML = '';
    document.getElementById('totalPrizesCount').textContent = '0 prizes';
    document.getElementById('modalLotteryTitle').textContent = 'Lottery Prizes';
}

// Modal functions for winners
function showWinnersModal() {
    document.getElementById('winnersModal').style.display = 'flex';
}

function closeWinnersModal() {
    document.getElementById('winnersModal').style.display = 'none';
    resetWinnersModalContent();
}

function resetWinnersModalContent() {
    document.getElementById('winnersContent').style.display = 'none';
    document.getElementById('winnersLoadingSpinner').style.display = 'block';
    document.getElementById('noWinnersMessage').style.display = 'none';
    document.getElementById('winnersTableBody').innerHTML = '';
    document.getElementById('winnersStats').innerHTML = '';
    document.getElementById('userTypeFilter').innerHTML = '';
    document.getElementById('activeFilters').innerHTML = '';
    document.getElementById('totalWinnersCount').textContent = '0 winners';
    document.getElementById('totalWinnersSummary').textContent = 'Total Winners: 0';
    document.getElementById('winnersFooterInfo').textContent = 'Showing 0 entries';
    currentWinnersData = [];
    currentFilter = 'all';
}

// Close modals when clicking outside
document.getElementById('prizeModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closePrizeModal();
    }
});

document.getElementById('winnersModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeWinnersModal();
    }
});

// Add event listeners to all view prizes buttons
document.querySelectorAll('.view-prizes-btn').forEach(button => {
    button.addEventListener('click', function(e) {
        e.preventDefault();
        
        const lotteryId = this.getAttribute('data-lottary-id');
        const lotteryTitle = this.getAttribute('data-lottary-title');
        const prizesUrl = this.getAttribute('data-prizes-url');
        
        // Set modal title
        document.getElementById('modalLotteryTitle').textContent = lotteryTitle + ' - Prizes';
        
        // Reset and show modal
        resetPrizeModalContent();
        showPrizeModal();
        
        // Fetch prizes
        fetchPrizes(prizesUrl);
    });
});

// Add event listeners to all view winners buttons
document.querySelectorAll('.view-winners-btn').forEach(button => {
    button.addEventListener('click', function(e) {
        e.preventDefault();
        
        const lotteryId = this.getAttribute('data-lottary-id');
        const lotteryTitle = this.getAttribute('data-lottary-title');
        const winnersUrl = this.getAttribute('data-winners-url');
        
        // Set global variables
        currentLotteryId = lotteryId;
        currentLotteryTitle = lotteryTitle;
        
        // Set modal title
        document.getElementById('modalWinnersTitle').textContent = lotteryTitle + ' - Winners';
        
        // Reset and show modal
        resetWinnersModalContent();
        showWinnersModal();
        
        // Fetch winners
        fetchWinners(winnersUrl);
    });
});

// Function to fetch prizes
function fetchPrizes(url) {
    // Show loading
    document.getElementById('prizeLoadingSpinner').style.display = 'block';
    document.getElementById('prizesContent').style.display = 'none';
    document.getElementById('noPrizesMessage').style.display = 'none';
    
    // Make AJAX request
    fetch(url, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        // Hide loading
        document.getElementById('prizeLoadingSpinner').style.display = 'none';
        
        if (data.status && data.data && data.data.length > 0) {
            // Clear previous content
            const tableBody = document.getElementById('prizesTableBody');
            tableBody.innerHTML = '';
            
            // Populate table with prizes
            data.data.forEach((prize, index) => {
                const row = document.createElement('tr');
                row.style.borderBottom = '1px solid #eee';
                row.style.transition = 'background 0.3s';
                row.onmouseover = () => row.style.background = '#f8f9fa';
                row.onmouseout = () => row.style.background = 'transparent';
                
                // Format winner number
                let winnerNumber = prize.winner_number;
                let winnerNumberHtml = '<span style="color: #999; font-style: italic;">-</span>';
                if (winnerNumber && winnerNumber !== "null" && winnerNumber !== "0") {
                    winnerNumberHtml = `
                        <span style="display:inline-block; padding:6px 12px; border-radius:20px; background:linear-gradient(135deg,#28a745,#20c997); color:white; font-weight:600; font-size:12px; box-shadow:0 2px 4px rgba(40,167,69,0.2);">
                            ${winnerNumber}
                        </span>
                    `;
                }
                
                // Format draw type
                let drawType = prize.drew_type || 'Not specified';
                let drawTypeHtml = `
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <svg width="16" height="16" fill="#6c757d" viewBox="0 0 16 16" style="flex-shrink: 0;">
                            <path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71V3.5z"/>
                            <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0z"/>
                        </svg>
                        <span style="color: #495057; font-weight: 500; text-transform: capitalize;">${drawType}</span>
                    </div>
                `;
                
                // Format description with tooltip
                let description = prize.description || 'No description';
                let descriptionHtml = description;
                if (description.length > 50) {
                    descriptionHtml = `
                        <span style="cursor: help; display: inline-block; max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; border-bottom: 1px dotted #666;" 
                              title="${description.replace(/"/g, '&quot;')}">
                            ${description.substring(0, 50)}...
                        </span>
                    `;
                }
                
                // Format photo
                let photoHtml = '<span style="color: #999; font-style: italic;">No image</span>';
                if (prize.photo_url) {
                    photoHtml = `
                        <div style="text-align: center;">
                            <img src="${prize.photo_url}" 
                                 alt="${prize.name}" 
                                 style="width:60px; height:60px; object-fit:cover; border-radius:8px; border:2px solid #e9ecef; cursor:pointer; transition:all 0.3s;"
                                 onmouseover="this.style.transform='scale(1.1)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)'"
                                 onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none'"
                                 onclick="window.open('${prize.photo_url}', '_blank')"
                                 onerror="this.onerror=null; this.src='https://via.placeholder.com/60x60?text=No+Image'">
                        </div>
                    `;
                }
                
                row.innerHTML = `
                    <td style="padding: 15px; text-align: center; vertical-align: middle;">
                        <span style="display:inline-block; width:32px; height:32px; line-height:32px; background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); color:white; border-radius:50%; font-weight:600;">
                            ${index + 1}
                        </span>
                    </td>
                    <td style="padding: 15px; vertical-align: middle;">
                        <div style="font-weight: 600; color: #333; margin-bottom: 4px;">${prize.name}</div>
                        <div style="font-size: 12px; color: #999;">ID: ${prize.id}</div>
                    </td>
                    <td style="padding: 15px; vertical-align: middle;">
                        <span style="display:inline-block; padding:8px 16px; border-radius:20px; background:linear-gradient(135deg,#17a2b8,#20c997); color:white; font-weight:600; font-size:14px; box-shadow:0 2px 8px rgba(23,162,184,0.2);">
                            ${prize.prize_value}
                        </span>
                    </td>
                    <td style="padding: 15px; text-align: center; vertical-align: middle;">
                        ${winnerNumberHtml}
                    </td>
                    <td style="padding: 15px; vertical-align: middle;">
                        ${drawTypeHtml}
                    </td>
                    <td style="padding: 15px; vertical-align: middle; color: #555;">
                        ${descriptionHtml}
                    </td>
                    <td style="padding: 15px; text-align: center; vertical-align: middle;">
                        ${photoHtml}
                    </td>
                `;
                
                tableBody.appendChild(row);
            });
            
            // Update summary
            const totalCount = data.total;
            document.getElementById('totalPrizesCount').textContent = 
                totalCount + ' ' + (totalCount === 1 ? 'prize' : 'prizes');
            
            // Show content
            document.getElementById('prizesContent').style.display = 'block';
            document.getElementById('noPrizesMessage').style.display = 'none';
        } else {
            // Show no prizes message
            document.getElementById('prizesContent').style.display = 'none';
            document.getElementById('noPrizesMessage').style.display = 'block';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('prizeLoadingSpinner').style.display = 'none';
        
        // Show error message
        const tableBody = document.getElementById('prizesTableBody');
        tableBody.innerHTML = `
            <tr>
                <td colspan="7" style="padding: 40px 20px; text-align: center; color: #dc3545;">
                    <div style="margin-bottom: 15px;">
                        <svg width="48" height="48" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                            <path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995z"/>
                        </svg>
                    </div>
                    <h4 style="margin: 0 0 10px 0; color: #dc3545;">Failed to Load Prizes</h4>
                    <p style="margin: 0 0 20px 0; color: #6c757d;">${error.message}</p>
                    <button onclick="location.reload()" 
                            style="background: linear-gradient(135deg, #6c757d 0%, #495057 100%); color: white; border: none; padding: 8px 20px; border-radius: 6px; cursor: pointer; font-weight: 500;">
                        Reload Page
                    </button>
                </td>
            </tr>
        `;
        document.getElementById('prizesContent').style.display = 'block';
        document.getElementById('noPrizesMessage').style.display = 'none';
    });
}

// Function to fetch winners
function fetchWinners(url) {
    // Show loading
    document.getElementById('winnersLoadingSpinner').style.display = 'block';
    document.getElementById('winnersContent').style.display = 'none';
    document.getElementById('noWinnersMessage').style.display = 'none';
    
    // Make AJAX request
    fetch(url, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Winners data:', data);
        
        // Hide loading
        document.getElementById('winnersLoadingSpinner').style.display = 'none';
        
        if (data.success && data.data && data.data.length > 0) {
            // Store the data globally
            currentWinnersData = data.data;
            
            // Render the winners
            renderWinners();
            
            // Calculate and display statistics
            displayStatistics();
            
            // Add export button to header
            const exportBtnContainer = document.getElementById('exportBtnContainer');
            exportBtnContainer.innerHTML = `
                <button onclick="exportWinnersToCSV()" 
                        style="background: rgba(255,255,255,0.9); color: #28a745; border: none; padding: 6px 16px; border-radius: 20px; font-weight: 600; font-size: 14px; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: all 0.3s;"
                        onmouseover="this.style.transform='translateY(-2px)'; this.style.background='white'"
                        onmouseout="this.style.transform='translateY(0)'; this.style.background='rgba(255,255,255,0.9)'">
                    <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                        <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                    </svg>
                    Export
                </button>
            `;
            
            // Show content
            document.getElementById('winnersContent').style.display = 'block';
            document.getElementById('noWinnersMessage').style.display = 'none';
        } else {
            // Show no winners message
            document.getElementById('winnersContent').style.display = 'none';
            document.getElementById('noWinnersMessage').style.display = 'block';
        }
    })
    .catch(error => {
        console.error('Error fetching winners:', error);
        document.getElementById('winnersLoadingSpinner').style.display = 'none';
        
        // Show error message
        const tableBody = document.getElementById('winnersTableBody');
        tableBody.innerHTML = `
            <tr>
                <td colspan="8" style="padding: 40px 20px; text-align: center; color: #dc3545;">
                    <div style="margin-bottom: 15px;">
                        <svg width="48" height="48" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                            <path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995z"/>
                        </svg>
                    </div>
                    <h4 style="margin: 0 0 10px 0; color: #dc3545;">Failed to Load Winners</h4>
                    <p style="margin: 0 0 20px 0; color: #6c757d;">${error.message}</p>
                    <button onclick="location.reload()" 
                            style="background: linear-gradient(135deg, #6c757d 0%, #495057 100%); color: white; border: none; padding: 8px 20px; border-radius: 6px; cursor: pointer; font-weight: 500;">
                        Reload Page
                    </button>
                </td>
            </tr>
        `;
        document.getElementById('winnersContent').style.display = 'block';
        document.getElementById('noWinnersMessage').style.display = 'none';
    });
}

// Function to render winners based on current filter
function renderWinners() {
    // Filter winners based on current filter
    let filteredWinners = currentWinnersData;
    if (currentFilter === 'real') {
        filteredWinners = currentWinnersData.filter(winner => winner.user_type === 'Real');
    } else if (currentFilter === 'fake') {
        filteredWinners = currentWinnersData.filter(winner => winner.user_type === 'Fake');
    }
    
    // Clear previous content
    const tableBody = document.getElementById('winnersTableBody');
    tableBody.innerHTML = '';
    
    // Populate table with winners
    filteredWinners.forEach((winner, index) => {
        const row = document.createElement('tr');
        row.style.borderBottom = '1px solid #eee';
        row.style.transition = 'background 0.3s';
        row.onmouseover = () => row.style.background = '#f8f9fa';
        row.onmouseout = () => row.style.background = 'transparent';
        
        // Get winner information
        let winnerName = 'Unknown';
        let winnerLocation = 'Unknown';
        
        if (winner.user_type === 'Real' && winner.user) {
            winnerName = winner.user.name || 'Unknown';
            if (winner.user.city || winner.user.state || winner.user.country) {
                winnerLocation = `${winner.user.city || ''}${winner.user.city && winner.user.state ? ', ' : ''}${winner.user.state || ''}${(winner.user.city || winner.user.state) && winner.user.country ? ', ' : ''}${winner.user.country || ''}`;
            }
        } else if (winner.user_type === 'Fake' && winner.fake_person) {
            winnerName = winner.fake_person.full_name || 'Unknown';
            winnerLocation = `${winner.fake_person.upazila || ''}, ${winner.fake_person.district || ''}, ${winner.fake_person.country || ''}`;
        }
        
        // Format user type badge
        let userTypeBadge = '';
        if (winner.user_type === 'Real') {
            userTypeBadge = '<span class="user-type-badge user-type-real">Real User</span>';
        } else {
            userTypeBadge = '<span class="user-type-badge user-type-fake">Fake User</span>';
        }
        
        // Format prize photo
        let prizePhotoHtml = '<span style="color: #999; font-style: italic;">No image</span>';
        if (winner.prize && winner.prize.photo) {
            prizePhotoHtml = `
                <div style="text-align: center;">
                    <img src="${winner.prize.photo}" 
                         alt="${winner.prize.prize_value}" 
                         style="width:60px; height:60px; object-fit:cover; border-radius:8px; border:2px solid #e9ecef; cursor:pointer; transition:all 0.3s;"
                         onmouseover="this.style.transform='scale(1.1)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)'"
                         onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none'"
                         onclick="window.open('${winner.prize.photo}', '_blank')"
                         onerror="this.onerror=null; this.src='https://via.placeholder.com/60x60?text=No+Image'">
                </div>
            `;
        }
        
        row.innerHTML = `
            <td style="padding: 15px; text-align: center; vertical-align: middle;">
                <span style="display:inline-block; width:32px; height:32px; line-height:32px; background:linear-gradient(135deg,#28a745 0%,#20c997 100%); color:white; border-radius:50%; font-weight:600;">
                    ${index + 1}
                </span>
            </td>
            <td style="padding: 15px; vertical-align: middle;">
                <div style="font-weight: 600; color: #333; margin-bottom: 4px;">${winnerName}</div>
                <div style="font-size: 12px; color: #999;">ID: ${winner.id}</div>
            </td>
            <td style="padding: 15px; vertical-align: middle;">
                ${userTypeBadge}
            </td>
            <td style="padding: 15px; vertical-align: middle; color: #555;">
                ${winnerLocation}
            </td>
            <td style="padding: 15px; vertical-align: middle;">
                <span style="display:inline-block; padding:6px 12px; background:#f8f9fa; border-radius:6px; font-family:monospace; font-weight:500; color:#495057; border:1px solid #dee2e6;">
                    ${winner.ticket_number}
                </span>
            </td>
            <td style="padding: 15px; vertical-align: middle;">
                <div style="font-weight: 600; color: #333; margin-bottom: 4px;">${winner.prize ? winner.prize.prize_value : 'No Prize'}</div>
                ${winner.prize && winner.prize.winner_number ? `
                    <div style="font-size: 12px; color: #28a745; font-weight: 500;">
                        Winner Position: ${winner.prize.winner_number}
                    </div>
                ` : ''}
            </td>
            <td style="padding: 15px; text-align: center; vertical-align: middle;">
                ${prizePhotoHtml}
            </td>
            <td style="padding: 15px; text-align: center; vertical-align: middle;">
                ${winner.prize && winner.prize.winner_number ? `
                    <span style="display:inline-block; width:32px; height:32px; line-height:32px; background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); color:white; border-radius:50%; font-weight:600;">
                        ${winner.prize.winner_number}
                    </span>
                ` : '<span style="color: #999; font-style: italic;">-</span>'}
            </td>
        `;
        
        tableBody.appendChild(row);
    });
    
    // Update summary information
    document.getElementById('totalWinnersCount').textContent = filteredWinners.length + ' winners';
    document.getElementById('totalWinnersSummary').textContent = `Total Winners: ${filteredWinners.length}`;
    document.getElementById('winnersFooterInfo').textContent = `Showing ${filteredWinners.length} entries`;
    
    // Update active filters display
    const activeFilters = document.getElementById('activeFilters');
    activeFilters.innerHTML = '';
    if (currentFilter !== 'all') {
        const filterText = currentFilter === 'real' ? 'Real Users Only' : 'Fake Users Only';
        activeFilters.innerHTML = `
            <span style="background: #28a745; color: white; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 500; display: flex; align-items: center; gap: 6px;">
                <svg width="12" height="12" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 1A.5.5 0 0 1 6 14.5V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5v-2z"/>
                </svg>
                ${filterText}
            </span>
        `;
    }
}

// Function to display statistics
function displayStatistics() {
    const statsContainer = document.getElementById('winnersStats');
    
    // Calculate statistics
    const totalWinners = currentWinnersData.length;
    const realUsers = currentWinnersData.filter(w => w.user_type === 'Real').length;
    const fakeUsers = currentWinnersData.filter(w => w.user_type === 'Fake').length;
    const uniquePrizes = [...new Set(currentWinnersData.map(w => w.prize ? w.prize.prize_value : 'Unknown'))].length;
    
    // Create statistics cards
    statsContainer.innerHTML = `
        <div class="stat-card">
            <div class="stat-value" style="color: #28a745;">${totalWinners}</div>
            <div class="stat-label">Total Winners</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color: #17a2b8;">${realUsers}</div>
            <div class="stat-label">Real Users</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color: #6c757d;">${fakeUsers}</div>
            <div class="stat-label">Fake Users</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color: #764ba2;">${uniquePrizes}</div>
            <div class="stat-label">Unique Prizes</div>
        </div>
    `;
    
    // Create filter buttons
    const filterContainer = document.getElementById('userTypeFilter');
    filterContainer.innerHTML = `
        <span style="color: #495057; font-weight: 500;">Filter by:</span>
        <button class="filter-btn ${currentFilter === 'all' ? 'active' : ''}" onclick="setFilter('all')">
            All Users (${totalWinners})
        </button>
        <button class="filter-btn ${currentFilter === 'real' ? 'active' : ''}" onclick="setFilter('real')">
            Real Users (${realUsers})
        </button>
        <button class="filter-btn ${currentFilter === 'fake' ? 'active' : ''}" onclick="setFilter('fake')">
            Fake Users (${fakeUsers})
        </button>
    `;
}

// Function to set filter
function setFilter(filter) {
    currentFilter = filter;
    renderWinners();
}

// Function to export winners to CSV
function exportWinnersToCSV() {
    // Filter winners based on current filter
    let winnersToExport = currentWinnersData;
    if (currentFilter === 'real') {
        winnersToExport = currentWinnersData.filter(winner => winner.user_type === 'Real');
    } else if (currentFilter === 'fake') {
        winnersToExport = currentWinnersData.filter(winner => winner.user_type === 'Fake');
    }
    
    if (winnersToExport.length === 0) {
        alert('No winners to export!');
        return;
    }
    
    // Create CSV headers (removed User Type and Winner Number columns)
    let csv = 'No.,Winner Name,Location,Ticket Number,Prize\n';
    
    // Add data rows
    winnersToExport.forEach((winner, index) => {
        let winnerName = 'Unknown';
        let winnerLocation = 'Unknown';
        
        if (winner.user_type === 'Real' && winner.user) {
            winnerName = winner.user.name || 'Unknown';
            if (winner.user.city || winner.user.state || winner.user.country) {
                winnerLocation = `${winner.user.city || ''}${winner.user.city && winner.user.state ? ', ' : ''}${winner.user.state || ''}${(winner.user.city || winner.user.state) && winner.user.country ? ', ' : ''}${winner.user.country || ''}`;
            }
        } else if (winner.user_type === 'Fake' && winner.fake_person) {
            winnerName = winner.fake_person.full_name || 'Unknown';
            winnerLocation = `${winner.fake_person.upazila || ''}, ${winner.fake_person.district || ''}, ${winner.fake_person.country || ''}`;
        }
        
        const prizeValue = winner.prize ? winner.prize.prize_value : 'No Prize';
        
        // Escape commas and quotes
        const escapeCSV = (str) => {
            if (!str) return '';
            return `"${str.toString().replace(/"/g, '""')}"`;
        };
        
        csv += `${index + 1},${escapeCSV(winnerName)},${escapeCSV(winnerLocation)},${escapeCSV(winner.ticket_number)},${escapeCSV(prizeValue)}\n`;
    });
    
    // Create a blob and download link
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    
    link.setAttribute('href', url);
    link.setAttribute('download', `winners_${currentLotteryId}_${currentLotteryTitle.replace(/[^a-z0-9]/gi, '_').toLowerCase()}_${currentFilter}.csv`);
    link.style.visibility = 'hidden';
    
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    // Show success message
    showExportSuccess();
}

// Function to show export success message
function showExportSuccess() {
    // Create success message
    const message = document.createElement('div');
    message.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        padding: 15px 25px;
        border-radius: 8px;
        box-shadow: 0 10px 30px rgba(40,167,69,0.3);
        z-index: 1060;
        display: flex;
        align-items: center;
        gap: 12px;
        animation: slideIn 0.3s ease-out;
    `;
    
    message.innerHTML = `
        <svg width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
        </svg>
        <div>
            <div style="font-weight: 600; font-size: 16px;">Export Successful!</div>
            <div style="font-size: 13px; opacity: 0.9;">CSV file has been downloaded</div>
        </div>
    `;
    
    document.body.appendChild(message);
    
    // Add CSS for animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
    `;
    document.head.appendChild(style);
    
    // Remove message after 3 seconds
    setTimeout(() => {
        message.style.animation = 'slideOut 0.3s ease-in forwards';
        setTimeout(() => {
            document.body.removeChild(message);
        }, 300);
    }, 3000);
}
</script>
@endsection