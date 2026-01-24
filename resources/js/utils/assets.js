export function asset( path ) {
    return `${import.meta.env.VITE_APP_BASE_URL || ''}/storage/${path}`.replace( /([^:]\/)\/+/g, "$1" );
}
