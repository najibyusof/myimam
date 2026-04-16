import Swal from "sweetalert2";

// ---------------------------------------------------------------------------
// Base configuration — shared by all non-toast dialogs
// ---------------------------------------------------------------------------
export const swalBase = {
    customClass: {
        popup: "rounded-xl shadow-lg",
        title: "text-lg font-semibold text-gray-800",
        htmlContainer: "text-sm text-gray-600",
        actions: "gap-3",
        confirmButton:
            "bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg ml-1",
        cancelButton:
            "bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium px-4 py-2 rounded-lg mr-1",
    },
    buttonsStyling: false,
};

// ---------------------------------------------------------------------------
// Toast mixin
// ---------------------------------------------------------------------------
const toastTitles = {
    success: "Berjaya",
    error: "Ralat",
    warning: "Amaran",
    info: "Makluman",
    question: "Soalan",
};

const toastMixin = Swal.mixin({
    toast: true,
    position: "top-end",
    showConfirmButton: false,
    timer: 2600,
    timerProgressBar: true,
    customClass: {
        popup: "rounded-lg shadow-md text-sm",
    },
    didOpen: (el) => {
        el.addEventListener("mouseenter", Swal.stopTimer);
        el.addEventListener("mouseleave", Swal.resumeTimer);
    },
});

// ---------------------------------------------------------------------------
// Exported helpers
// ---------------------------------------------------------------------------

/**
 * Top-right toast notification.
 * @param {'success'|'error'|'warning'|'info'} icon
 * @param {string} text  — the message body
 */
export function showToast(icon, text) {
    return toastMixin.fire({
        icon,
        title: toastTitles[icon] ?? "",
        text,
    });
}

/**
 * Success modal (requires user to click OK).
 */
export function showSuccess(message) {
    return Swal.fire({
        ...swalBase,
        icon: "success",
        title: "Berjaya",
        text: message,
        confirmButtonText: "OK",
    });
}

/**
 * Error modal.
 */
export function showError(message) {
    return Swal.fire({
        ...swalBase,
        icon: "error",
        title: "Ralat",
        text: message,
        confirmButtonText: "OK",
    });
}

/**
 * Confirmation dialog.
 * @param {object} options
 * @param {string} [options.title]
 * @param {string} [options.text]
 * @param {string} [options.confirmText]
 */
export function showConfirm(options = {}) {
    return Swal.fire({
        ...swalBase,
        icon: "warning",
        title: options.title ?? "Adakah anda pasti?",
        text: options.text ?? "Tindakan ini tidak boleh dibatalkan.",
        showCancelButton: true,
        confirmButtonText: options.confirmText ?? "Ya, teruskan",
        cancelButtonText: "Batal",
        reverseButtons: true,
        focusCancel: true,
    });
}

/**
 * Confirmation dialog with built-in async processing state.
 * Shows loading spinner and disables confirm interaction while processing.
 * @param {object} options
 * @param {string} [options.title]
 * @param {string} [options.text]
 * @param {string} [options.confirmText]
 * @param {string} [options.processingError]
 * @param {() => Promise<unknown>} onConfirm
 */
export function showConfirmAsync(options = {}, onConfirm) {
    return Swal.fire({
        ...swalBase,
        icon: "warning",
        title: options.title ?? "Adakah anda pasti?",
        text: options.text ?? "Tindakan ini tidak boleh dibatalkan.",
        showCancelButton: true,
        confirmButtonText: options.confirmText ?? "Ya, teruskan",
        cancelButtonText: "Batal",
        reverseButtons: true,
        focusCancel: true,
        showLoaderOnConfirm: true,
        allowOutsideClick: () => !Swal.isLoading(),
        allowEscapeKey: () => !Swal.isLoading(),
        preConfirm: async () => {
            try {
                return await onConfirm();
            } catch (error) {
                Swal.showValidationMessage(
                    options.processingError ||
                        "Ralat semasa memproses tindakan.",
                );
                throw error;
            }
        },
    });
}

/**
 * Success modal that auto-closes and can optionally redirect after closing.
 * @param {string} message
 * @param {{redirectUrl?: string, timer?: number}} options
 */
export function showSuccessAutoClose(message, options = {}) {
    const timer = Number(options.timer ?? 1400);
    const redirectUrl = options.redirectUrl || "";

    return Swal.fire({
        ...swalBase,
        icon: "success",
        title: "Berjaya",
        text: message,
        showConfirmButton: false,
        timer,
        timerProgressBar: true,
    }).then((result) => {
        if (redirectUrl) {
            window.location.assign(redirectUrl);
        }
        return result;
    });
}

/**
 * Full-screen loading overlay (call Swal.close() when done).
 */
export function showLoading(
    title = "Memproses...",
    text = "Sila tunggu sebentar.",
) {
    Swal.fire({
        ...swalBase,
        title,
        text,
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        },
    });
}
