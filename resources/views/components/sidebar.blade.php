@php
    $user = auth()->user();   
    $roleIds = $user?->roles?->pluck('id')->all() ?? [];
    $isAcademicRole = in_array(2, $roleIds, true)
        || in_array(3, $roleIds, true)
        || in_array(4, $roleIds, true);

    $currentRoute = request()->routeIs('*') ? request()->route()->getName() : '';

    $activeBase = 'bg-green-100 text-green-700 border-l-4 border-green-600 font-semibold';
    $inactiveBase = 'text-gray-600 hover:bg-gray-100 hover:text-gray-800 transition-colors';

    function isActive($routeName, $currentRoute, $activeBase, $inactiveBase) {
        $routePrefix = Str::before($routeName, '.');
        if (request()->is($routePrefix . '*') || request()->routeIs($routePrefix . '*')) {
            return $activeBase;
        }
        return $inactiveBase;
    }
@endphp

<!-- HEADER DEL SIDEBAR -->
<div class="px-4 py-6 border-b border-gray-200">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-green-500 to-indigo-600 text-white flex items-center justify-center font-bold">
            <i class="fas fa-graduation-cap text-lg"></i>
        </div>
        <div>
            <h1 class="text-lg font-bold text-gray-900">Sistema Académico</h1>
           
        </div>
    </div>
</div>

<!-- NAVEGACIÓN PRINCIPAL -->
<nav class="flex-1 px-3 py-6 space-y-1 overflow-y-auto">

    {{-- DASHBOARD --}}
    <a href="{{ route('dashboard') }}" 
       class="flex items-center gap-3 px-4 py-2.5 rounded-lg {{ isActive('dashboard', $currentRoute, $activeBase, $inactiveBase) }}"
       title="Vista general del sistema">
        <i class="fas fa-chart-line text-base flex-shrink-0"></i>
        <span class="text-sm font-medium">Dashboard</span>
    </a>

    {{-- SECCIÓN: GESTIÓN ACADÉMICA --}}
    <div class="mt-6 mb-3">
        <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Académico</p>
    </div>

    {{-- MATERIAS --}}
    @if($isAcademicRole || $user->hasPermission('materias.index'))
    <a href="{{ route('materias.index') }}" 
       class="flex items-center gap-3 px-4 py-2.5 rounded-lg {{ isActive('materias.index', $currentRoute, $activeBase, $inactiveBase) }}"
       title="Gestión de Materias">
        <i class="fas fa-book text-base flex-shrink-0"></i>
        <span class="text-sm font-medium">Materias</span>
    </a>
    @endif

    {{-- SECUENCIAS --}}
    @if($isAcademicRole || $user->hasPermission('secuencias.index'))
    <a href="{{ route('secuencias.index') }}" 
       class="flex items-center gap-3 px-4 py-2.5 rounded-lg {{ isActive('secuencias.index', $currentRoute, $activeBase, $inactiveBase) }}"
       title="Gestión de Secuencias Didácticas">
        <i class="fas fa-layer-group text-base flex-shrink-0"></i>
        <span class="text-sm font-medium">Secuencias</span>
    </a>
    @endif

    {{-- CARRERAS --}}
    @if($isAcademicRole || $user->hasPermission('carreras.index'))
    <a href="{{ route('carreras.index') }}" 
       class="flex items-center gap-3 px-4 py-2.5 rounded-lg {{ isActive('carreras.index', $currentRoute, $activeBase, $inactiveBase) }}"
       title="Gestión de Carreras">
        <i class="fas fa-university text-base flex-shrink-0"></i>
        <span class="text-sm font-medium">Carreras</span>
    </a>
    @endif

    {{-- SECCIÓN: ADMINISTRACIÓN --}}
    <div class="mt-6 mb-3">
        <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Administración</p>
    </div>

    {{-- USUARIOS --}}
    @if($user->hasPermission('usuarios.index'))
    <a href="{{ route('usuarios.index') }}" 
       class="flex items-center gap-3 px-4 py-2.5 rounded-lg {{ isActive('usuarios.index', $currentRoute, $activeBase, $inactiveBase) }}"
       title="Gestión de Usuarios">
        <i class="fas fa-users text-base flex-shrink-0"></i>
        <span class="text-sm font-medium">Usuarios</span>
    </a>
    @endif

    {{-- ROLES --}}
    @if($user->hasPermission('roles.index'))
    <a href="{{ route('roles.index') }}" 
       class="flex items-center gap-3 px-4 py-2.5 rounded-lg {{ isActive('roles.index', $currentRoute, $activeBase, $inactiveBase) }}"
       title="Gestión de Roles">
        <i class="fas fa-shield-alt text-base flex-shrink-0"></i>
        <span class="text-sm font-medium">Roles</span>
    </a>
    @endif

    {{-- PERMISOS --}}
    @if($user->hasPermission('permisos.index'))
    <a href="{{ route('permisos.index') }}" 
       class="flex items-center gap-3 px-4 py-2.5 rounded-lg {{ isActive('permisos.index', $currentRoute, $activeBase, $inactiveBase) }}"
       title="Gestión de Permisos">
        <i class="fas fa-lock text-base flex-shrink-0"></i>
        <span class="text-sm font-medium">Permisos</span>
    </a>
    @endif

</nav>
