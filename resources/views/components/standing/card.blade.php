 @props(['user', 'position' => 1])

 @php
     $color = match ($position) {
         1 => 'amber',
         2 => 'slate',
         3 => 'orange',
     };
 @endphp

 <div
     {{ $attributes->class([
             'bg-amber-500/5 border-amber-500/80 ring-amber-500/30 order-2 -top-2' => $position === 1, // Ouro
             'bg-slate-400/5 border-slate-400/80 ring-slate-400/30 order-1' => $position === 2, // Prata
             'bg-amber-700/5 border-amber-700/80 ring-amber-700/30 order-3' => $position === 3, // Bronze
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

     <flux:heading size="xl" class="tabular-nums">
         {{ $user->points }}{{ str('pt')->plural($user->points) }}
     </flux:heading>

     <flux:text
         variant="strong"
         class="w-full text-white wrap-break-word"
     >
         {{ Number::currency($user->award) }}
     </flux:text>

     <flux:badge
         size="lg"
         variant="solid"
         rounded
         :$color
         class="absolute text-white w-8 -top-4"
     >
         {{ $position }}
     </flux:badge>
 </div>
