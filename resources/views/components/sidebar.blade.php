@php

    $user = auth()->user();   
    $roleIds = $user?->roles?->pluck('id')->all() ?? [];
    $isAcademicRole = in_array(2, $roleIds, true)
        || in_array(3, $roleIds, true)
        || in_array(4, $roleIds, true);

    $currentRoute = request()->routeIs('*') ? request()->route()->getName() : '';


    $activeBase = 'bg-white/20 border-l-4 border-[var(--c2)] text-white font-semibold shadow-md';
 
    $inactiveBase = 'opacity-85 hover:opacity-100 hover:bg-white/10 text-white';

    function isActive($routeName, $currentRoute, $activeBase, $inactiveBase) {
        $routePrefix = Str::before($routeName, '.');
        if (request()->is($routePrefix . '*') || request()->routeIs($routePrefix . '*')) {
            return $activeBase;
        }
        return $inactiveBase;
    }
@endphp

<div class="flex flex-col items-center justify-center pt-2 pb-6 mb-8 border-b border-white/10">
    <div class="w-12 h-12 rounded-full bg-white/10 flex items-center justify-center ring-2 ring-[var(--c3)] mb-3 shadow-lg">
        <i class="fas fa-user-shield text-xl text-white opacity-90"></i>
    </div>
    <h2 class="text-xl font-extrabold tracking-wider text-white uppercase">Panel {{ $user->roles?->first()?->nombre ?? 'Usuario' }}</h2>
</div>

<nav class="flex flex-col gap-1.5 overflow-y-auto max-h-[calc(100vh-160px)] pr-2">

    {{-- Dashboard siempre visible --}}
    <a href="{{ route('dashboard') }}" 
         class="flex items-center gap-4 px-4 py-2.5 rounded-lg transition-all duration-300 ease-in-out {{ isActive('dashboard', $currentRoute, $activeBase, $inactiveBase) }}"
       title="Vista general del sistema">
        <div class="w-6 text-center">
            <i class="fas fa-tachometer-alt text-lg"></i>
        </div>
        <span class="font-medium tracking-wide">Dashboard</span>
    </a>

    <p class="text-xs text-white/40 font-semibold uppercase mt-4 mb-1 pl-4">Gestión de Contenido</p>


 {{-- Materias --}}
     @if($isAcademicRole || $user->hasPermission('materias.index'))
    <a href="{{ route('materias.index') }}" 
       class="flex items-center gap-4 px-4 py-2.5 rounded-lg transition-all duration-300 ease-in-out {{ isActive('materias.index', $currentRoute, $activeBase, $inactiveBase) }}"
       title="Gestión de Materias">
        <div class="w-6 text-center">
            <i class="fas fa-book-open text-lg"></i>
        </div>
        <span class="font-medium tracking-wide">Materias</span>
    </a>
    @endif

    {{-- Secuencias --}}
    @if($isAcademicRole || $user->hasPermission('secuencias.index'))
    <a href="{{ route('secuencias.index') }}" 
       class="flex items-center gap-4 px-4 py-2.5 rounded-lg transition-all duration-300 ease-in-out {{ isActive('secuencias.index', $currentRoute, $activeBase, $inactiveBase) }}"
       title="Gestión de Secuencias">
        <div class="w-6 text-center">
            <i class="fas fa-layer-group text-lg"></i>
        </div>
        <span class="font-medium tracking-wide">Secuencias</span>
    </a>
    @endif

    {{-- Carreas --}}
    @if($isAcademicRole || $user->hasPermission('carreras.index'))
    <a href="{{ route('carreras.index') }}" 
       class="flex items-center gap-4 px-4 py-2.5 rounded-lg transition-all duration-300 ease-in-out {{ isActive('carreras.index', $currentRoute, $activeBase, $inactiveBase) }}"
       title="Gestión de Materias">
        <div class="w-6 text-center">
            <i class="fas fa-graduation-cap text-lg"></i>  <!-- birrete, el más común para carreras -->
        </div>
        <span class="font-medium tracking-wide">Carreras</span>
    </a>
    @endif

    <p class="text-xs text-white/40 font-semibold uppercase mt-4 mb-1 pl-4">Administración del Sistema</p>

    {{-- Roles --}}
    @if($user->hasPermission('roles.index'))
    <a href="{{ route('roles.index') }}" 
       class="flex items-center gap-4 px-4 py-2.5 rounded-lg transition-all duration-300 ease-in-out {{ isActive('roles.index', $currentRoute, $activeBase, $inactiveBase) }}"
       title="Gestión de Roles de Usuario">
        <div class="w-6 text-center">
            <i class="fas fa-user-tag text-lg"></i>
        </div>
        <span class="font-medium tracking-wide">Roles</span>
    </a>
    @endif

    {{-- Usuarios --}}
    @if($user->hasPermission('usuarios.index'))
    <a href="{{ route('usuarios.index') }}" 
       class="flex items-center gap-4 px-4 py-2.5 rounded-lg transition-all duration-300 ease-in-out {{ isActive('usuarios.index', $currentRoute, $activeBase, $inactiveBase) }}"
       title="Gestión de Cuentas de Usuario">
        <div class="w-6 text-center">
            <i class="fas fa-users text-lg"></i>
        </div>
        <span class="font-medium tracking-wide">Usuarios</span>
    </a>
    @endif

    {{-- Permisos --}}
    @if($user->hasPermission('permisos.index'))
    <a href="{{ route('permisos.index') }}" 
       class="flex items-center gap-4 px-4 py-2.5 rounded-lg transition-all duration-300 ease-in-out {{ isActive('permisos.index', $currentRoute, $activeBase, $inactiveBase) }}"
       title="Definición y Asignación de Permisos">
        <div class="w-6 text-center">
            <i class="fas fa-key text-lg"></i>
        </div>
        <span class="font-medium tracking-wide">Permisos</span>
    </a>
    @endif

</nav>