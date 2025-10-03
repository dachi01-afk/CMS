<div
    class="p-4 bg-white rounded-xl shadow-lg border border-gray-100 transition duration-300 hover:shadow-xl w-full h-full flex flex-col justify-between">
    <div class="flex items-start justify-between">
        <div class="flex items-center space-x-3">
            <div class="p-3 rounded-full text-blue-600 bg-blue-100/70">
                {!! $iconSvg !!}
            </div>
            <h3 class="text-sm font-semibold text-gray-800 flex items-center">
                {{ $title }}
            </h3>
        </div>
    </div>

    <div class="mt-4">
        <p class="text-4xl font-extrabold text-gray-900 leading-none" data-role="value">
            {{ $value }}
        </p>
    </div>

    <div class="mt-3 flex items-center">
        <div class="flex items-center text-xs font-medium">
            <span class="font-bold mr-1" data-role="percentage">
                {{ $percentage }}
            </span>
            <span class="text-gray-500" data-role="context">
                {{ $context }}
            </span>
        </div>
    </div>
</div>
