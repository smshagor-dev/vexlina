@extends('backend.layouts.app')

@section('content')
<div style="max-width: 1400px; margin: 0 auto; padding: 20px;">
    <!-- Header Section -->
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; padding: 25px 30px; margin-bottom: 30px; color: white; box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
            <div style="display: flex; align-items: center; gap: 15px;">
                <div style="background: rgba(255, 255, 255, 0.2); width: 50px; height: 50px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                    <svg width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                    </svg>
                </div>
                <div>
                    <h1 style="margin: 0; font-size: 24px; font-weight: 700;">{{ $lottaryTitle }}</h1>
                    <p style="margin: 5px 0 0 0; opacity: 0.9; font-size: 14px;">
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="margin-right: 5px;">
                            <path d="M14 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h12zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z"/>
                            <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022z"/>
                        </svg>
                        {{ $tickets->count() }} tickets purchased
                    </p>
                </div>
            </div>
            <div style="display: flex; gap: 10px;">
                <a href="{{ url()->previous() }}" 
                   style="background: rgba(255, 255, 255, 0.2); color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 500; display: flex; align-items: center; gap: 8px; transition: all 0.3s;"
                   onmouseover="this.style.background='rgba(255, 255, 255, 0.3)'; this.style.transform='translateY(-2px)'"
                   onmouseout="this.style.background='rgba(255, 255, 255, 0.2)'; this.style.transform='translateY(0)'">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                    </svg>
                    Back
                </a>
                <button onclick="window.print()" 
                        style="background: rgba(255, 255, 255, 0.2); color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 500; display: flex; align-items: center; gap: 8px; cursor: pointer; transition: all 0.3s;"
                        onmouseover="this.style.background='rgba(255, 255, 255, 0.3)'; this.style.transform='translateY(-2px)'"
                        onmouseout="this.style.background='rgba(255, 255, 255, 0.2)'; this.style.transform='translateY(0)'">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M5 1a2 2 0 0 0-2 2v1h10V3a2 2 0 0 0-2-2H5zm6 8H5a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1z"/>
                        <path d="M0 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-1v-2a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v2H2a2 2 0 0 1-2-2V7zm2.5 1a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
                    </svg>
                    Print
                </button>
            </div>
        </div>
    </div>

    @if($tickets->count())
    <!-- Stats Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div style="background: white; border-radius: 12px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); border-left: 4px solid #28a745;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <div style="font-size: 12px; color: #6c757d; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 5px;">Total Tickets</div>
                    <div style="font-size: 32px; font-weight: 700; color: #28a745;">{{ $tickets->count() }}</div>
                </div>
                <div style="background: rgba(40, 167, 69, 0.1); width: 50px; height: 50px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                    <svg width="24" height="24" fill="#28a745" viewBox="0 0 16 16">
                        <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1H2zm13 2.383-4.708 2.825L15 11.105V5.383zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741zM1 11.105l4.708-2.897L1 5.383v5.722z"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <div style="background: white; border-radius: 12px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); border-left: 4px solid #17a2b8;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <div style="font-size: 12px; color: #6c757d; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 5px;">Unique Users</div>
                    <div style="font-size: 32px; font-weight: 700; color: #17a2b8;">{{ $tickets->pluck('user_id')->unique()->count() }}</div>
                </div>
                <div style="background: rgba(23, 162, 184, 0.1); width: 50px; height: 50px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                    <svg width="24" height="24" fill="#17a2b8" viewBox="0 0 16 16">
                        <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H3zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <div style="background: white; border-radius: 12px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); border-left: 4px solid #ffc107;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <div style="font-size: 12px; color: #6c757d; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 5px;">Recent Activity</div>
                    <div style="font-size: 32px; font-weight: 700; color: #ffc107;">
                        @if($tickets->isNotEmpty())
                            {{ $tickets->first()->created_at->diffForHumans() }}
                        @else
                            No activity
                        @endif
                    </div>
                </div>
                <div style="background: rgba(255, 193, 7, 0.1); width: 50px; height: 50px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                    <svg width="24" height="24" fill="#ffc107" viewBox="0 0 16 16">
                        <path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71V3.5z"/>
                        <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Tickets Table -->
    <div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.08);">
        <div style="padding: 20px 30px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="background: #f8f9fa; width: 40px; height: 40px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                    <svg width="20" height="20" fill="#495057" viewBox="0 0 16 16">
                        <path d="M1.5 0A1.5 1.5 0 0 0 0 1.5v13A1.5 1.5 0 0 0 1.5 16h13a1.5 1.5 0 0 0 1.5-1.5V1.5A1.5 1.5 0 0 0 14.5 0h-13zm1 2h3v1h-3V2zm0 2h3v1h-3V4zm0 2h3v1h-3V6zm4-4h6v1h-6V2zm0 2h6v1h-6V4zm0 2h6v1h-6V6zM1.5 8h13a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-6a.5.5 0 0 1 .5-.5zM2 9v5h12V9H2z"/>
                    </svg>
                </div>
                <h3 style="margin: 0; font-size: 18px; font-weight: 600; color: #495057;">Ticket Details</h3>
            </div>
            <div style="display: flex; align-items: center; gap: 15px;">
                <div style="position: relative;">
                    <input type="text" id="searchInput" placeholder="Search tickets..." 
                           style="padding: 10px 15px 10px 40px; border: 1px solid #dee2e6; border-radius: 8px; width: 250px; font-size: 14px; transition: all 0.3s;"
                           onfocus="this.style.borderColor='#667eea'; this.style.boxShadow='0 0 0 3px rgba(102, 126, 234, 0.1)'"
                           onblur="this.style.borderColor='#dee2e6'; this.style.boxShadow='none'">
                    <svg width="16" height="16" fill="#6c757d" viewBox="0 0 16 16" 
                         style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%);">
                        <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                        <th style="padding: 18px 15px; text-align: left; border-bottom: 2px solid #dee2e6; color: #495057; font-weight: 600; min-width: 80px;">#</th>
                        <th style="padding: 18px 15px; text-align: left; border-bottom: 2px solid #dee2e6; color: #495057; font-weight: 600; min-width: 150px;">Ticket Number</th>
                        <th style="padding: 18px 15px; text-align: left; border-bottom: 2px solid #dee2e6; color: #495057; font-weight: 600; min-width: 200px;">User Name</th>
                        <th style="padding: 18px 15px; text-align: left; border-bottom: 2px solid #dee2e6; color: #495057; font-weight: 600; min-width: 250px;">User Email</th>
                        <th style="padding: 18px 15px; text-align: left; border-bottom: 2px solid #dee2e6; color: #495057; font-weight: 600; min-width: 180px;">Purchase Date</th>
                        <th style="padding: 18px 15px; text-align: center; border-bottom: 2px solid #dee2e6; color: #495057; font-weight: 600; min-width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="ticketsTableBody">
                    @foreach($tickets as $index => $ticket)
                    <tr style="border-bottom: 1px solid #eee; transition: background 0.3s;" 
                        onmouseover="this.style.background='#f8f9fa'" 
                        onmouseout="this.style.background='transparent'"
                        data-search="{{ strtolower($ticket->ticket_number . ' ' . ($ticket->user->name ?? '') . ' ' . ($ticket->user->email ?? '')) }}"
                        data-ticket-number="{{ $ticket->ticket_number }}"
                        data-user-name="{{ $ticket->user->name ?? 'User deleted' }}"
                        data-user-email="{{ $ticket->user->email ?? '-' }}"
                        data-purchase-date="{{ $ticket->created_at->format('Y-m-d H:i:s') }}"
                        data-user-id="{{ $ticket->user->id ?? '-' }}"
                        data-ticket-id="{{ $ticket->id }}">
                        <td style="padding: 20px 15px; color: #666; font-weight: 500;">
                            <span style="display:inline-block; width:32px; height:32px; line-height:32px; background:linear-gradient(135deg, #667eea 0%, #764ba2 100%); color:white; border-radius:8px; text-align:center; font-weight:600;">
                                {{ $index + 1 }}
                            </span>
                        </td>
                        <td style="padding: 20px 15px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="background: rgba(102, 126, 234, 0.1); width: 40px; height: 40px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                    <svg width="20" height="20" fill="#667eea" viewBox="0 0 16 16">
                                        <path d="M14 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h12zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z"/>
                                        <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022z"/>
                                    </svg>
                                </div>
                                <div>
                                    <div style="font-weight: 600; color: #333; font-size: 15px;">{{ $ticket->ticket_number }}</div>
                                    <div style="font-size: 12px; color: #6c757d;">Ticket ID: {{ $ticket->id }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 20px 15px;">
                            @if($ticket->user)
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 14px;">
                                    {{ substr($ticket->user->name, 0, 1) }}
                                </div>
                                <div>
                                    <div style="font-weight: 500; color: #333;">{{ $ticket->user->name }}</div>
                                    <div style="font-size: 12px; color: #6c757d;">User ID: {{ $ticket->user->id }}</div>
                                </div>
                            </div>
                            @else
                            <div style="color: #999; font-style: italic; display: flex; align-items: center; gap: 8px;">
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                    <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
                                </svg>
                                User deleted
                            </div>
                            @endif
                        </td>
                        <td style="padding: 20px 15px;">
                            @if($ticket->user)
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="background: rgba(255, 193, 7, 0.1); width: 40px; height: 40px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                    <svg width="20" height="20" fill="#ffc107" viewBox="0 0 16 16">
                                        <path d="M.05 3.555A2 2 0 0 1 2 2h12a2 2 0 0 1 1.95 1.555L8 8.414.05 3.555ZM0 4.697v7.104l5.803-3.558L0 4.697ZM6.761 8.83l-6.57 4.027A2 2 0 0 0 2 14h12a2 2 0 0 0 1.808-1.144l-6.57-4.027L8 9.586l-1.239-.757Zm3.436-.586L16 11.801V4.697l-5.803 3.546Z"/>
                                    </svg>
                                </div>
                                <div>
                                    <div style="font-weight: 500; color: #333;">{{ $ticket->user->email }}</div>
                                    @if($ticket->user->email_verified_at)
                                    <div style="font-size: 12px; color: #28a745;">
                                        <svg width="12" height="12" fill="currentColor" viewBox="0 0 16 16" style="margin-right: 4px;">
                                            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                                        </svg>
                                        Verified
                                    </div>
                                    @else
                                    <div style="font-size: 12px; color: #dc3545;">
                                        <svg width="12" height="12" fill="currentColor" viewBox="0 0 16 16" style="margin-right: 4px;">
                                            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                            <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
                                        </svg>
                                        Not verified
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @else
                            <div style="color: #999; font-style: italic;">-</div>
                            @endif
                        </td>
                        <td style="padding: 20px 15px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="background: rgba(108, 117, 125, 0.1); width: 40px; height: 40px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                    <svg width="20" height="20" fill="#6c757d" viewBox="0 0 16 16">
                                        <path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71V3.5z"/>
                                        <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <div style="font-weight: 600; color: #333;">{{ $ticket->created_at->format('M d, Y') }}</div>
                                    <div style="font-size: 13px; color: #6c757d;">
                                        {{ $ticket->created_at->format('h:i A') }}
                                        <span style="margin-left: 8px; color: #999;">({{ $ticket->created_at->diffForHumans() }})</span>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 20px 15px; text-align: center;">
                            <div style="display: flex; gap: 8px; justify-content: center;">
                                <button onclick="viewTicketDetails('{{ $ticket->id }}')"
                                        style="background: rgba(102, 126, 234, 0.1); border: none; width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s;"
                                        onmouseover="this.style.background='rgba(102, 126, 234, 0.2)'; this.style.transform='translateY(-2px)'"
                                        onmouseout="this.style.background='rgba(102, 126, 234, 0.1)'; this.style.transform='translateY(0)'"
                                        title="View Details">
                                    <svg width="16" height="16" fill="#667eea" viewBox="0 0 16 16">
                                        <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0z"/>
                                        <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8zm8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z"/>
                                    </svg>
                                </button>
                                @if($ticket->user)
                                <button onclick="sendEmailToUser('{{ $ticket->user->email }}')"
                                        style="background: rgba(23, 162, 184, 0.1); border: none; width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s;"
                                        onmouseover="this.style.background='rgba(23, 162, 184, 0.2)'; this.style.transform='translateY(-2px)'"
                                        onmouseout="this.style.background='rgba(23, 162, 184, 0.1)'; this.style.transform='translateY(0)'"
                                        title="Send Email">
                                    <svg width="16" height="16" fill="#17a2b8" viewBox="0 0 16 16">
                                        <path d="M.05 3.555A2 2 0 0 1 2 2h12a2 2 0 0 1 1.95 1.555L8 8.414.05 3.555ZM0 4.697v7.104l5.803-3.558L0 4.697ZM6.761 8.83l-6.57 4.027A2 2 0 0 0 2 14h12a2 2 0 0 0 1.808-1.144l-6.57-4.027L8 9.586l-1.239-.757Zm3.436-.586L16 11.801V4.697l-5.803 3.546Z"/>
                                    </svg>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Table Footer -->
        <div style="padding: 20px 30px; border-top: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;">
            <div style="color: #6c757d; font-size: 14px;">
                Showing {{ $tickets->count() }} tickets
            </div>
            <div>
                <button onclick="exportToCSV()"
                        style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 500; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: all 0.3s;"
                        onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(40, 167, 69, 0.3)'"
                        onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                        <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                    </svg>
                    Export CSV
                </button>
            </div>
        </div>
    </div>
    @else
    <!-- No Tickets Found -->
    <div style="background: white; border-radius: 12px; padding: 60px 30px; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.08);">
        <div style="width: 100px; height: 100px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 30px;">
            <svg width="48" height="48" fill="#adb5bd" viewBox="0 0 16 16">
                <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1H2zm13 2.383-4.708 2.825L15 11.105V5.383zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741zM1 11.105l4.708-2.897L1 5.383v5.722z"/>
            </svg>
        </div>
        <h3 style="margin: 0 0 15px 0; color: #6c757d; font-weight: 600;">No Tickets Found</h3>
        <p style="margin: 0 0 30px 0; color: #adb5bd; max-width: 500px; margin: 0 auto 30px;">
            No tickets have been purchased for this lottery yet. Share the lottery to get participants!
        </p>
        <a href="{{ url()->previous() }}"
           style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 12px 30px; border-radius: 8px; text-decoration: none; font-weight: 500; display: inline-flex; align-items: center; gap: 10px; transition: all 0.3s;"
           onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 15px rgba(102, 126, 234, 0.4)'"
           onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
            <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
            </svg>
            Go Back
        </a>
    </div>
    @endif
</div>

<script>
// Search functionality
document.getElementById('searchInput')?.addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('#ticketsTableBody tr');
    
    rows.forEach(row => {
        const searchData = row.getAttribute('data-search');
        if (searchData.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

// View ticket details
function viewTicketDetails(ticketId) {
    alert('Viewing details for ticket ID: ' + ticketId);
    // You can implement a modal here to show more details
}

// Send email to user
function sendEmailToUser(email) {
    window.location.href = 'mailto:' + email + '?subject=Ticket%20Information';
}

// Export to CSV
function exportToCSV() {
    // Collect data from table rows
    const rows = document.querySelectorAll('#ticketsTableBody tr');
    const ticketsData = [];
    
    rows.forEach((row, index) => {
        if (row.style.display !== 'none') {
            const ticketNumber = row.getAttribute('data-ticket-number');
            const userName = row.getAttribute('data-user-name');
            const userEmail = row.getAttribute('data-user-email');
            const purchaseDate = row.getAttribute('data-purchase-date');
            const userId = row.getAttribute('data-user-id');
            const ticketId = row.getAttribute('data-ticket-id');
            
            ticketsData.push({
                ticketNumber: ticketNumber,
                userName: userName,
                userEmail: userEmail,
                purchaseDate: purchaseDate,
                userId: userId,
                ticketId: ticketId
            });
        }
    });
    
    // Prepare CSV content
    const headers = ['Ticket Number', 'User Name', 'User Email', 'Purchase Date', 'User ID', 'Ticket ID'];
    const csvRows = [];
    
    // Add headers
    csvRows.push(headers.join(','));
    
    // Add data rows
    ticketsData.forEach(ticket => {
        const row = [
            `"${ticket.ticketNumber}"`,
            `"${ticket.userName}"`,
            `"${ticket.userEmail}"`,
            `"${ticket.purchaseDate}"`,
            `"${ticket.userId}"`,
            `"${ticket.ticketId}"`
        ];
        csvRows.push(row.join(','));
    });
    
    // Create CSV string
    const csvContent = csvRows.join('\n');
    
    // Create and download CSV file
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement("a");
    const url = URL.createObjectURL(blob);
    link.setAttribute("href", url);
    link.setAttribute("download", "lottery_tickets_{{ str_replace(' ', '_', $lottaryTitle) }}.csv");
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Print functionality
document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
        e.preventDefault();
        window.print();
    }
});
</script>

<style>
@media print {
    .no-print { display: none !important; }
    body { background: white !important; }
    div { box-shadow: none !important; }
    table { border: 1px solid #ddd !important; }
    th { background: #f5f5f5 !important; }
}
</style>
@endsection