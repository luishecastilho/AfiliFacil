/**
 * AfiliFacil brand mark.
 *
 * The source PNG has a transparent background. By default the mark sits on a
 * white plate so it stays legible on dark or colored surfaces. On surfaces that
 * are already white, pass `plate={false}` to render the bare transparent PNG.
 */
export function Logo({ size = 32, plate = true, rounded = 'rounded-lg', className = '' }) {
    if (!plate) {
        return (
            <img
                src="/logo.png"
                alt="AfiliFacil"
                className={`object-contain ${className}`}
                style={{ width: size, height: size }}
            />
        );
    }

    return (
        <span
            className={`inline-flex items-center justify-center bg-white ${rounded} ${className}`}
            style={{ width: size, height: size, padding: Math.round(size * 0.1) }}
        >
            <img src="/logo.png" alt="AfiliFacil" className="h-full w-full object-contain" />
        </span>
    );
}
