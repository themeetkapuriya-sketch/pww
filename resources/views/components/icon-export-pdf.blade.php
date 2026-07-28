@props(['class' => 'w-6 h-6 shrink-0'])

<svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 28" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path d="M 2.5 3 C 2.5 1.343 3.843 0 5.5 0 L 15.5 0 L 23.5 8 L 23.5 25 C 23.5 26.657 22.157 28 20.5 28 L 5.5 28 C 3.843 28 2.5 26.657 2.5 25 Z" fill="#DC2626"/>
    <path d="M 15.5 0 L 23.5 8 L 15.5 8 Z" fill="#F87171"/>
    <text x="13" y="19" font-family="system-ui, -apple-system, sans-serif" font-weight="900" font-size="7.5" fill="#FFFFFF" text-anchor="middle" letter-spacing="-0.2">PDF</text>
</svg>
