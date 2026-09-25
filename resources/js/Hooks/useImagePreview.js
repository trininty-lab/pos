import { useEffect, useRef, useState } from "react";

export default function useImagePreview(initialPreview = null) {
    const [preview, setPreview] = useState(initialPreview);
    const objectUrlRef = useRef(null);

    const releaseObjectUrl = () => {
        if (objectUrlRef.current) {
            URL.revokeObjectURL(objectUrlRef.current);
            objectUrlRef.current = null;
        }
    };

    const selectFile = (file) => {
        if (!file) return null;

        releaseObjectUrl();
        const objectUrl = URL.createObjectURL(file);
        objectUrlRef.current = objectUrl;
        setPreview(objectUrl);

        return objectUrl;
    };

    const setGeneratedPreview = (nextPreview) => {
        if (nextPreview !== objectUrlRef.current) {
            releaseObjectUrl();
        }
        setPreview(nextPreview);
    };

    const resetPreview = () => {
        setGeneratedPreview(initialPreview);
    };

    useEffect(() => releaseObjectUrl, []);

    return { preview, selectFile, setGeneratedPreview, resetPreview };
}