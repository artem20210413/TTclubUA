<style>
    .icon-toggle {
        width: 83px;
        height: 83px;
        cursor: pointer;
        display: inline-block;
        position: relative;
    }

    .icon-toggle svg {
        position: absolute;
        top: 0; left: 0;
        width: 100%;
        height: 100%;
        transition: opacity 0.2s ease;
    }

    .icon-toggle svg.alt {
        opacity: 0;
    }

    .icon-toggle:hover svg.alt,
    .icon-toggle:active svg.alt {
        opacity: 1;
    }

    .icon-toggle:hover svg.default,
    .icon-toggle:active svg.default {
        opacity: 0;
    }
</style>

<div class="icon-toggle">
    <!-- обычная иконка -->
    <svg class="default" width="83" height="83" viewBox="0 0 83 83" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="41.5" cy="41.5" r="41.5" fill="#303234"/>
        <circle cx="41.5" cy="41.5001" r="32.7389" fill="#26292D"/>
        <path d="M49.45 38.6714C51.45 39.8261 51.45 42.7129 49.45 43.8676L39.4291 49.6531C37.4291 50.8078 34.9291 49.3644 34.9291 47.055L34.9291 35.484C34.9291 33.1745 37.4291 31.7312 39.4291 32.8859L49.45 38.6714Z" fill="white"/>
    </svg>

    <!-- активная иконка -->
    <svg class="alt" width="83" height="83" viewBox="0 0 83 83" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="41.5" cy="41.5" r="40.5" fill="#303234" stroke="white" stroke-width="2"/>
        <circle cx="41.5" cy="41.5001" r="32.7389" fill="#26292D"/>
        <path d="M49.45 38.6714C51.45 39.8261 51.45 42.7129 49.45 43.8676L39.4292 49.6531C37.4292 50.8078 34.9292 49.3644 34.9292 47.055L34.9292 35.484C34.9292 33.1745 37.4292 31.7312 39.4292 32.8859L49.45 38.6714Z" fill="white"/>
    </svg>
</div>
