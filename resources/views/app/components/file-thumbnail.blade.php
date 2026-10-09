<svg xmlns="http://www.w3.org/2000/svg" width="480" height="320" viewBox="0 0 480 320">
    <rect width="480" height="320" fill="#edf3fa"/>
    <rect x="175" y="55" width="130" height="180" rx="12" fill="white" stroke="#bacce3" stroke-width="4"/>
    @if ($file->category === 'video' || $file->category === 'audio')
        <path d="M220 100 L220 185 L275 143 Z" fill="#4879bd"/>
    @else
        <path d="M200 100 H280 M200 130 H280 M200 160 H255" stroke="#4879bd" stroke-width="8"/>
    @endif
    <text x="240" y="275" text-anchor="middle" font-family="sans-serif" font-size="24" fill="#4879bd">{{ strtoupper(mb_substr($file->extension, 0, 12)) }}</text>
</svg>
