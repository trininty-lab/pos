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

const CROP_AREA_SIZE = 320;
const MIN_ZOOM = 1;
const MAX_ZOOM = 4;

function createCropPreview(imageUrl, pixels, rotation) {
    return new Promise((resolve, reject) => {
        const image = new Image();
        image.onload = () => {
            const canvas = document.createElement("canvas");
            canvas.width = 320;
            canvas.height = 320;
            const context = canvas.getContext("2d");
            context.translate(160, 160);
            context.rotate((rotation * Math.PI) / 180);
            context.drawImage(
                image,
                pixels.x,
                pixels.y,
                pixels.width,
                pixels.height,
                -160,
                -160,
                320,
                320
            );
            resolve(canvas.toDataURL("image/webp", 0.85));
        };
        image.onerror = reject;
        image.src = imageUrl;
    });
}

export default function ProductImageEditor({
    value,
    onChange,
    error,
    existingPreview = null,
}) {
    const inputRef = useRef(null);
    const [sourceUrl, setSourceUrl] = useState(null);
    const [selectedFile, setSelectedFile] = useState(null);
    const [editorOpen, setEditorOpen] = useState(false);
    const { preview, selectFile, setGeneratedPreview, resetPreview } =
        useImagePreview(existingPreview);
    const [crop, setCrop] = useState({ x: 0, y: 0 });
    const [zoom, setZoom] = useState(MIN_ZOOM);
    const [rotation, setRotation] = useState(0);
    const [croppedAreaPixels, setCroppedAreaPixels] = useState(null);

    useEffect(() => {
        return () => {
            if (sourceUrl) URL.revokeObjectURL(sourceUrl);
        };
    }, [sourceUrl]);

    useEffect(() => {
        if (!value) setGeneratedPreview(existingPreview);
    }, [existingPreview, value]);

    const openFile = (file) => {
        if (!file || !file.type.startsWith("image/")) return;
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
        if (!selectedFile || !croppedAreaPixels) return;

        try {
            setGeneratedPreview(await createCropPreview(sourceUrl, croppedAreaPixels, rotation));
        } catch {
            setGeneratedPreview(sourceUrl);
        }
        onChange({
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
        onChange({ file: null, crop: null, remove: true });
    };

    return (
        <div>
            <div className="mb-4 aspect-square overflow-hidden rounded-xl border-2 border-dashed border-slate-300 bg-slate-100 dark:border-slate-700 dark:bg-slate-800">
                {preview ? (
                    <img src={preview} alt="Product preview" className="h-full w-full object-cover" />
                ) : (
                    <div className="flex h-full items-center justify-center p-6 text-center">
                        <div>
                            <IconPhoto size={48} className="mx-auto mb-2 text-slate-400" />
                            <p className="text-sm text-slate-500">No image yet</p>
                        </div>
                    </div>
                )}
            </div>
            <div className="flex flex-wrap gap-2">
                <button type="button" onClick={() => inputRef.current?.click()} className="inline-flex items-center gap-2 rounded-lg bg-primary-500 px-3 py-2 text-sm font-medium text-white hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <IconUpload size={17} />
                    {preview ? "Change image" : "Upload image"}
                </button>
                {preview && (
                    <button type="button" onClick={clearImage} className="inline-flex items-center gap-2 rounded-lg border border-danger-200 px-3 py-2 text-sm font-medium text-danger-600 hover:bg-danger-50 focus:outline-none focus:ring-2 focus:ring-danger-500">
                        <IconX size={17} />
                        Remove
                    </button>
                )}
            </div>
            <input ref={inputRef} type="file" className="sr-only" accept="image/jpeg,image/png,image/webp" onChange={(event) => { openFile(event.target.files?.[0]); event.target.value = ""; }} />
            <p className="mt-2 text-xs text-slate-500">JPEG, PNG or WebP. Maximum 5 MB, minimum 300 x 300 px.</p>
            {error && <p className="mt-1 text-sm text-danger-600">{error}</p>}

            {editorOpen && sourceUrl && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4" role="dialog" aria-modal="true" aria-label="Crop product image">
                    <div className="w-full max-w-lg rounded-2xl bg-white p-5 shadow-2xl dark:bg-slate-900">
                        <div className="mb-4 flex items-center justify-between">
                            <div>
                                <h2 className="text-lg font-semibold text-slate-900 dark:text-white">Crop product image</h2>
                                <p className="text-sm text-slate-500">Drag to reposition. The final image is square.</p>
                            </div>
                            <button type="button" onClick={() => setEditorOpen(false)} className="rounded-lg p-2 text-slate-500 hover:bg-slate-100" aria-label="Cancel crop"><IconX size={20} /></button>
                        </div>
                        <div className="relative mx-auto overflow-hidden rounded-xl bg-slate-950" style={{ width: CROP_AREA_SIZE, height: CROP_AREA_SIZE }}>
                            <Cropper image={sourceUrl} crop={crop} zoom={zoom} rotation={rotation} aspect={1} cropSize={{ width: CROP_AREA_SIZE, height: CROP_AREA_SIZE }} onCropChange={setCrop} onZoomChange={setZoom} onCropComplete={(_, pixels) => setCroppedAreaPixels(pixels)} showGrid />
                        </div>
                        <div className="mt-4 flex items-center justify-center gap-3">
                            <button type="button" onClick={() => setZoom(Math.max(MIN_ZOOM, zoom - 0.25))} className="rounded-lg border p-2 text-slate-600 hover:bg-slate-100" aria-label="Zoom out"><IconZoomOut size={19} /></button>
                            <input type="range" min={MIN_ZOOM} max={MAX_ZOOM} step="0.05" value={zoom} onChange={(event) => setZoom(Number(event.target.value))} aria-label="Zoom" className="w-48" />
                            <button type="button" onClick={() => setZoom(Math.min(MAX_ZOOM, zoom + 0.25))} className="rounded-lg border p-2 text-slate-600 hover:bg-slate-100" aria-label="Zoom in"><IconZoomIn size={19} /></button>
                            <button type="button" onClick={() => setRotation((rotation + 90) % 360)} className="rounded-lg border p-2 text-slate-600 hover:bg-slate-100" aria-label="Rotate image"><IconRotateClockwise size={19} /></button>
                            <button type="button" onClick={() => { setCrop({ x: 0, y: 0 }); setZoom(MIN_ZOOM); setRotation(0); }} className="rounded-lg border p-2 text-slate-600 hover:bg-slate-100" aria-label="Reset crop"><IconRefresh size={19} /></button>
                        </div>
                        <div className="mt-5 flex justify-end gap-2">
                            <button type="button" onClick={() => setEditorOpen(false)} className="inline-flex items-center gap-2 rounded-lg border px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100"><IconX size={17} />Cancel</button>
                            <button type="button" onClick={applyCrop} disabled={!croppedAreaPixels} className="inline-flex items-center gap-2 rounded-lg bg-primary-500 px-4 py-2 text-sm font-medium text-white hover:bg-primary-600 disabled:cursor-not-allowed disabled:opacity-50"><IconCheck size={17} />Apply crop</button>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
