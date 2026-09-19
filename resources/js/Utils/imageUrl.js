/**
 * Get proper image URL - handles both full URLs and filenames
 * @param {string} image - Image path (can be filename or full URL)
 * @param {string} folder - Storage folder (products, categories, etc)
 * @returns {string|null} - Proper image URL or null
 */
export function getImageUrl(image, folder = "products") {
    if (!image) return null;

    // If already a full URL, return as-is
    if (/^(https?:)?\/\//i.test(image) || image.startsWith("/")) {
        return image;
    }

    // Otherwise, prepend storage path
    return `/storage/${folder}/${image}`;
}

/**
 * Get product image URL
 * @param {string} image - Product image
 * @returns {string|null}
 */
export function getProductImageUrl(image, variant = "display") {
    if (image && typeof image === "object") {
        return image[variant] || image.display || image.large || null;
    }

    if (image === "default.jpg") return "/images/icon-512.png";

    return getImageUrl(image, "products");
}

/**
 * Get category image URL
 * @param {string} image - Category image
 * @returns {string|null}
 */
export function getCategoryImageUrl(image) {
    return image === "default.jpg"
        ? "/images/icon-512.png"
        : getImageUrl(image, "categories");
}

export default { getImageUrl, getProductImageUrl, getCategoryImageUrl };
