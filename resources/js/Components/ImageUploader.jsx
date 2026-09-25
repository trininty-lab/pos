import React, { useEffect, useRef, useState } from "react";
import Cropper from "react-easy-crop";
import {
    IconCheck,
    IconPhoto,
    IconRefresh,
    IconRotateClockwise,
    IconUpload,
    IconX,
    IconZoomIn,
    IconZoomOut,
} from "@tabler/icons-react";
import useImagePreview from "@/Hooks/useImagePreview";

const DEFAULT_MAX_SIZE = 5 * 1024 * 1024;
const MIN_ZOOM = 1;
const MAX_ZOOM = 4;

const DEFAULT_CONFIG = {
    imageType: "GENERAL_IMAGE",
    aspectRatio: 1,
    cropShape: "rect",
    maxFileSize: DEFAULT_MAX_SIZE,
    allowedFormats: ["image/jpeg", "image/png", "image/webp"],
    multiple: false,
    required: false,
    maxImages: 1,
    previewSize: 320,
    label: "Image",
};

function createCropPreview(imageUrl, pixels, rotation) {
    return new Promise((resolve, reject) => {
        const image = new Image();
        image.onload = () => {
            const canvas = document.createElement("canvas");
            const targetWidth = 320;
            const targetHeight = 320;
            canvas.width = targetWidth;
            canvas.height = targetHeight;
            const context = canvas.getContext("2d");
            context.translate(targetWidth / 2, targetHeight / 2);
            context.rotate((rotation * Math.PI) / 180);
            context.drawImage(
                image,
                pixels.x,
                pixels.y,
                pixels.width,
                pixels.height,
                -targetWidth / 2,
                -targetHeight / 2,
                targetWidth,
                targetHeight
            );
            resolve(canvas.toDataURL("image/webp", 0.85));
        };
        image.onerror = reject;
        image.src = imageUrl;
    });
}

function normalizeAspectRatio(aspectRatio) {
    if (typeof aspectRatio === "number") return aspectRatio;
    if (aspectRatio === "square") return 1;
    if (aspectRatio === "portrait") return 4 / 5;
    if (aspectRatio === "landscape") return 16 / 9;
    if (aspectRatio === "banner") return 16 / 6;
    return 1;
}

function validateFile(file, config) {
    if (! file) return "Please choose an image.";
    if (! file.type || ! config.allowedFormats.includes(file.type)) {
        return `Unsupported file type. Allowed: ${config.allowedFormats.map((type) => type.replace("image/", "")).join(", ")}.`;
    }
    if (file.size > config.maxFileSize) {
        return `Image must be smaller than ${(config.maxFileSize / (1024 * 1024)).toFixed(1)} MB.`;
    }

    return "";
}

export default function ImageUploader({
    value,
    onChange,
    error,
    existingPreview = null,
    imageType = DEFAULT_CONFIG.imageType,
    aspectRatio = DEFAULT_CONFIG.aspectRatio,
    cropShape = DEFAULT_CONFIG.cropShape,
    maxFileSize = DEFAULT_CONFIG.maxFileSize,
    allowedFormats = DEFAULT_CONFIG.allowedFormats,
    multiple = DEFAULT_CONFIG.multiple,
    required = DEFAULT_CONFIG.required,
    maxImages = DEFAULT_CONFIG.maxImages,
    previewSize = DEFAULT_CONFIG.previewSize,
    label = DEFAULT_CONFIG.label,
    placeholder = "No image yet",
}) {
    const inputRef = useRef(null);
    const [sourceUrl, setSourceUrl] = useState(null);
    const [selectedFile, setSelectedFile] = useState(null);
    const [editorOpen, setEditorOpen] = useState(false);
    const [validationError, setValidationError] = useState("");
    const { preview, selectFile, setGeneratedPreview, resetPreview } = useImagePreview(existingPreview);
    const [crop, setCrop] = useState({ x: 0, y: 0 });
    const [zoom, setZoom] = useState(MIN_ZOOM);
    const [rotation, setRotation] = useState(0);
    const [croppedAreaPixels, setCroppedAreaPixels] = useState(null);

    const config = {
        imageType,
        aspectRatio: normalizeAspectRatio(aspectRatio),
        cropShape,
        maxFileSize,
        allowedFormats,
        multiple,
        required,
        maxImages,
        previewSize,
        label,
    };

    useEffect(() => {
        return () => {
            if (sourceUrl) URL.revokeObjectURL(sourceUrl);
        };
    }, [sourceUrl]);

    useEffect(() => {
        if (! value) {
            setGeneratedPreview(existingPreview);
        }
    }, [existingPreview, value]);

    const openFile = (file) => {
        if (! file) return;

        const fileError = validateFile(file, config);
        setValidationError(fileError);
        if (fileError) return;

        if (sourceUrl) URL.revokeObjectURL(sourceUrl);

        setSourceUrl(selectFile(file));
        setSelectedFile(file);
        setCrop({ x: 0, y: 0 });
        setZoom(MIN_ZOOM);
        setRotation(0);
        setCroppedAreaPixels(null);
        setEditorOpen(true);
    };

    const applyCrop = async () => {
        if (! selectedFile || ! croppedAreaPixels) return;

        try {
            const generatedPreview = await createCropPreview(sourceUrl, croppedAreaPixels, rotation);
            setGeneratedPreview(generatedPreview);
        } catch {
            setGeneratedPreview(sourceUrl);
        }

        onChange?.({
            file: selectedFile,
            crop: {
                x: Math.round(croppedAreaPixels.x),
                y: Math.round(croppedAreaPixels.y),
                width: Math.round(croppedAreaPixels.width),
                height: Math.round(croppedAreaPixels.height),
                rotation,
            },
            remove: false,
        });
        setEditorOpen(false);
    };

    const clearImage = () => {
        if (sourceUrl) URL.revokeObjectURL(sourceUrl);
        setSourceUrl(null);
        setSelectedFile(null);
        resetPreview();
        setEditorOpen(false);
        setValidationError("");
        onChange?.({ file: null, crop: null, remove: true });
    };

    const blockClass = "aspect-square overflow-hidden rounded-xl border-2 border-dashed border-slate-300 bg-slate-100 dark:border-slate-700 dark:bg-slate-800";

    return (
        <div>
            <div className={`${blockClass} mb-4`} style={{ maxWidth: `${previewSize}px` }}>
                {preview ? (
                    <img src={preview} alt={label} className="h-full w-full object-cover" />
                ) : (
                    <div className="flex h-full items-center justify-center p-6 text-center">
                        <div>
                            <IconPhoto size={48} className="mx-auto mb-2 text-slate-400" />
                            <p className="text-sm text-slate-500">{placeholder}</p>
                        </div>
                    </div>
                )}
            </div>

            <div className="flex flex-wrap gap-2">
                <button
                    type="button"
                    onClick={() => inputRef.current?.click()}
                    className="inline-flex items-center gap-2 rounded-lg bg-primary-500 px-3 py-2 text-sm font-medium text-white hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-500"
                >
                    <IconUpload size={17} />
                    {preview ? `Change ${label}` : `Upload ${label}`}
                </button>

                {preview && (
                    <button
                        type="button"
                        onClick={clearImage}
                        className="inline-flex items-center gap-2 rounded-lg border border-danger-200 px-3 py-2 text-sm font-medium text-danger-600 hover:bg-danger-50 focus:outline-none focus:ring-2 focus:ring-danger-500"
                    >
                        <IconX size={17} />
                        Remove
                    </button>
                )}
            </div>

            <input
                ref={inputRef}
                type="file"
                className="sr-only"
                accept={config.allowedFormats.join(",")}
                multiple={multiple}
                required={required}
                onChange={(event) => {
                    const file = event.target.files?.[0];
                    if (file) openFile(file);
                    event.target.value = "";
                }}
            />

            <p className="mt-2 text-xs text-slate-500">
                {config.allowedFormats.map((type) => type.replace("image/", "")).join(", ").toUpperCase()}. Max {(config.maxFileSize / (1024 * 1024)).toFixed(1)}MB.
            </p>

            {(validationError || error) && (
                <p className="mt-1 text-sm text-danger-600">{validationError || error}</p>
            )}

            {editorOpen && sourceUrl && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4" role="dialog" aria-modal="true" aria-label={`Crop ${label}`}>
                    <div className="w-full max-w-lg rounded-2xl bg-white p-5 shadow-2xl dark:bg-slate-900">
                        <div className="mb-4 flex items-center justify-between">
                            <div>
                                <h2 className="text-lg font-semibold text-slate-900 dark:text-white">Crop {label}</h2>
                                <p className="text-sm text-slate-500">Drag to reposition. Adjust zoom and rotation if needed.</p>
                            </div>
                            <button
                                type="button"
                                onClick={() => setEditorOpen(false)}
                                className="rounded-lg p-2 text-slate-500 hover:bg-slate-100"
                                aria-label="Cancel crop"
                            >
                                <IconX size={20} />
                            </button>
                        </div>

                        <div className="relative mx-auto overflow-hidden rounded-xl bg-slate-950" style={{ width: config.previewSize, height: config.previewSize }}>
                            <Cropper
                                image={sourceUrl}
                                crop={crop}
                                zoom={zoom}
                                rotation={rotation}
                                aspect={config.aspectRatio}
                                cropShape={config.cropShape === "circle" ? "round" : "rect"}
                                cropSize={{ width: config.previewSize, height: config.previewSize }}
                                onCropChange={setCrop}
                                onZoomChange={setZoom}
                                onCropComplete={(_, pixels) => setCroppedAreaPixels(pixels)}
                                showGrid
                            />
                        </div>

                        <div className="mt-4 flex items-center justify-center gap-3">
                            <button type="button" onClick={() => setZoom(Math.max(MIN_ZOOM, zoom - 0.25))} className="rounded-lg border p-2 text-slate-600 hover:bg-slate-100" aria-label="Zoom out">
                                <IconZoomOut size={19} />
                            </button>
                            <input type="range" min={MIN_ZOOM} max={MAX_ZOOM} step="0.05" value={zoom} onChange={(event) => setZoom(Number(event.target.value))} aria-label="Zoom" className="w-48" />
                            <button type="button" onClick={() => setZoom(Math.min(MAX_ZOOM, zoom + 0.25))} className="rounded-lg border p-2 text-slate-600 hover:bg-slate-100" aria-label="Zoom in">
                                <IconZoomIn size={19} />
                            </button>
                            <button type="button" onClick={() => setRotation((rotation + 90) % 360)} className="rounded-lg border p-2 text-slate-600 hover:bg-slate-100" aria-label="Rotate image">
                                <IconRotateClockwise size={19} />
                            </button>
                            <button type="button" onClick={() => { setCrop({ x: 0, y: 0 }); setZoom(MIN_ZOOM); setRotation(0); }} className="rounded-lg border p-2 text-slate-600 hover:bg-slate-100" aria-label="Reset crop">
                                <IconRefresh size={19} />
                            </button>
                        </div>

                        <div className="mt-5 flex justify-end gap-2">
                            <button type="button" onClick={() => setEditorOpen(false)} className="inline-flex items-center gap-2 rounded-lg border px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100">
                                <IconX size={17} />Cancel
                            </button>
                            <button
                                type="button"
                                onClick={applyCrop}
                                disabled={!croppedAreaPixels}
                                className="inline-flex items-center gap-2 rounded-lg bg-primary-500 px-4 py-2 text-sm font-medium text-white hover:bg-primary-600 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <IconCheck size={17} />Apply crop
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
