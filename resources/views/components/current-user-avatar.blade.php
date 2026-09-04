@props(['user' => auth()->user()])

<span {{ $attributes->merge(['class' => 'grid shrink-0 place-items-center overflow-hidden rounded-full bg-[#153d36] font-extrabold text-white']) }}>
    @if ($user->avatar_path)
        <img src="{{ route('profile.avatar.show') }}" alt="Foto {{ $user->name }}" class="h-full w-full object-cover">
    @else
        {{ str($user->name)->substr(0, 2)->upper() }}
    @endif
</span>
