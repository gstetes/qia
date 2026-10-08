<template>
    <span
        class="pointer-events-none absolute inset-y-0 left-0 z-10 flex w-12 items-center justify-center text-muted-foreground transition-colors duration-200 group-focus-within:text-primary motion-reduce:transition-none [&>svg]:size-5"
        aria-hidden="true"
    >
        <slot />
    </span>
</template>
