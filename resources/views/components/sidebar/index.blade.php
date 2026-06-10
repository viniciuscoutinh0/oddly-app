 <flux:sidebar
     sticky
     collapsible="mobile"
     class="lg:hidden bg-zinc-900 border-r border-zinc-800"
 >
     <flux:sidebar.header>
         <flux:brand
             href="#"
             :name="config('app.name')"
         >
             <x-slot name="logo">
                 <div
                     class="size-6 rounded-md shrink-0 bg-accent text-accent-foreground flex items-center justify-center">
                     <span class="font-bold">O</span>
                 </div>
             </x-slot>
         </flux:brand>

         <flux:sidebar.collapse
             class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />
     </flux:sidebar.header>

     <flux:sidebar.nav>
         <flux:sidebar.item :href="route('dashboard')">
             Dashboard
         </flux:sidebar.item>

         <flux:sidebar.item :href="route('pools.index')">
             Bolões
         </flux:sidebar.item>
     </flux:sidebar.nav>
 </flux:sidebar>
