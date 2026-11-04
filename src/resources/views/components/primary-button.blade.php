<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-primario border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primario-HOVER focus:bg-primario-HOVER active:bg-primario-HOVER focus:outline-none focus:ring-2 focus:ring-primario focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
