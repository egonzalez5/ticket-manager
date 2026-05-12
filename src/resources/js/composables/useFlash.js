import { watch } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useToast } from 'primevue/usetoast'

const FLASH_CONFIG = {
    success: { severity: 'success', summary: 'Success', life: 4000 },
    error:   { severity: 'error',   summary: 'Error',   life: 6000 },
    warning: { severity: 'warn',    summary: 'Warning', life: 5000 },
    info:    { severity: 'info',    summary: 'Info',    life: 4000 },
}

export function useFlash() {
    const page  = usePage()
    const toast = useToast()

    watch(
        () => page.props.flash,
        (flash) => {
            if (!flash) return
            for (const [key, cfg] of Object.entries(FLASH_CONFIG)) {
                if (flash[key]) {
                    toast.add({ ...cfg, detail: flash[key] })
                }
            }
        },
        { deep: true },
    )
}
