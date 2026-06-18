 @props(['user', 'position' => 1])

 @php
     $color = match ($position) {
         1 => 'amber',
         2 => 'zinc',
         3 => 'orange',
     };
 @endphp

 <div
     {{ $attributes->class([
             'bg-amber-400/5 border-amber-500 ring-amber-500 order-2 -top-2' => $position === 1,
             'bg-zinc-400/5 border-zinc-400 ring-zinc-400 order-1' => $position === 2,
             'bg-orange-400/5 border-orange-400 ring-orange-400 order-3' => $position === 3,
         ])->merge([
             'class' =>
                 'relative flex flex-col items-center text-center rounded-xl gap-3 pt-9 px-2 pb-6 sm:px-6 ring-2 border',
         ]) }}>
     <flux:avatar
         :initials="$user->initials"
         circle
         size="lg"
         badge="🔥"
         badge:circle
         :$color
     />

     <flux:text class="w-full wrap-break-word">
         {{ $user->name }}
     </flux:text>

     <flux:heading size="xl">
         {{ $user->points }}
         {{ str('pt')->plural($user->points) }}
     </flux:heading>


     <flux:text class="w-full wrap-break-word">
         {{ Number::currency($user->award) }}
     </flux:text>


     <flux:badge
         size="lg"
         variant="solid"
         rounded
         :$color
         class="absolute -top-3"
     >{{ $position }}</flux:badge>
 </div>
