<div>
    @if($alert)
        <div class="fi-alert fi-alert-danger rounded-xl p-4 shadow-sm ring-1 ring-danger-600/20 dark:ring-danger-400/20" style="background-color: #fef2f2; border-left: 4px solid #dc2626;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <span style="font-size: 24px;">⚠️</span>
                <div>
                    <strong style="color: #991b1b; font-size: 16px;">WARNING: Overheating!</strong>
                    <p style="color: #b91c1c; margin: 0;">Suhu ruangan saat ini <strong>{{ number_format($temp, 1) }}°C</strong> melebihi batas aman (35°C). Segera lakukan tindakan!</p>
                </div>
            </div>
        </div>
    @else
        <div class="fi-alert fi-alert-success rounded-xl p-3 shadow-sm ring-1 ring-success-600/20 dark:ring-success-400/20" style="background-color: #f0fdf4; border-left: 4px solid #16a34a;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 20px;">✅</span>
                <span style="color: #166534; font-size: 14px; font-weight: 500;">Sistem Normal — Suhu dalam batas aman.</span>
            </div>
        </div>
    @endif
</div>
