{{-- Publish / unpublish button for $variant. $problems is $variant->publishProblems(). --}}
@if ($variant->is_published)
    <form action="{{ route('admin.cake-variants.unpublish', $variant) }}" method="POST"
          onsubmit="return confirm('Unpublish this drawing? The design page goes back to the layered preview for {{ $variant->layers }} layers.')">
        @csrf
        @method('DELETE')
        <button type="submit" class="text-xs text-ink/70 underline underline-offset-4 hover:text-clay">Unpublish</button>
    </form>
@else
    <form action="{{ route('admin.cake-variants.publish', $variant) }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-sm" @if ($problems) title="Fix the problems listed first" @endif>Publish</button>
    </form>
@endif
