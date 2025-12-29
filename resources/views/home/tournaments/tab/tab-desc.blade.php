@if ($tournament->description == null)
    <div class="text-center py-5">
        <i class="bi bi-info-circle me-2" style="font-size: 3rem; color: #444;"></i>
        <p class="mt-3">
            Chưa có mô tả cho giải đấu này.
        </p>
    </div>
@else
    <div class="text-center py-1">
        {!! $tournament->description !!}
    </div>
@endif
