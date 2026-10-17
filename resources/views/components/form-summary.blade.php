<dl class="divide-y divide-gray-200 rounded-lg border border-gray-200 p-4 text-sm dark:divide-gray-700 dark:border-gray-700">
    <template x-for="(fila, indice) in resumen" :key="indice">
        <div class="grid gap-1 py-2 sm:grid-cols-2">
            <dt class="font-medium" x-text="fila.etiqueta"></dt>
            <dd class="break-words whitespace-pre-wrap" x-text="fila.valor"></dd>
        </div>
    </template>
</dl>
