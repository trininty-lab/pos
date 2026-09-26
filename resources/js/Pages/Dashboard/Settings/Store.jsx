import DashboardLayout from "@/Layouts/DashboardLayout";
import { Head, useForm } from "@inertiajs/react";
import Input from "@/Components/Dashboard/Input";
import ImageUploader from "@/Components/ImageUploader";
import { getStorageImageUrl } from "@/Utils/imageUrl";
import Textarea from "@/Components/Dashboard/TextArea";
import toast from "react-hot-toast";
import {
    IconBuildingStore,
    IconDeviceFloppy,
    IconPhone,
    IconMapPin,
    IconWorld,
    IconMail,
    IconPhoto,
    IconFileCertificate,
    IconReceiptTax,
} from "@tabler/icons-react";

export default function Store({ settings }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        store_name: settings.store_name || "",
        store_logo: null,
        remove_store_logo: false,
        store_address: settings.store_address || "",
        store_phone: settings.store_phone || "",
        store_email: settings.store_email || "",
        store_website: settings.store_website || "",
        store_city: settings.store_city || "",
        store_npwp: settings.store_npwp || "",
        store_nib: settings.store_nib || "",
        tax_default_rate: settings.tax_default_rate || "11.00",
    });

    const submit = (e) => {
        e.preventDefault();
        post(route("settings.store.update"), {
            preserveScroll: true,
            onSuccess: () => {
                toast.success("Store profile saved");
                reset("store_logo");
            },
            onError: () => toast.error("Failed to save store profile"),
        });
    };

    return (
        <>
            <Head title="Store Profile" />

            <div className="space-y-6">
                <div>
                    <h1 className="text-2xl font-bold text-slate-900 dark:text-white">
                        Store Settings
                    </h1>
                    <p className="text-sm text-slate-500 dark:text-slate-400">
                        Configure the store identity shown on receipts and reports.
                    </p>
                </div>

                <form onSubmit={submit} className="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 space-y-6">
                    <div className="flex flex-col lg:flex-row gap-6">
                        {/* Logo */}
                        <div className="lg:w-1/3">
                            <label className="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3 flex items-center gap-2">
                                <IconPhoto size={18} />
                                Store Logo
                            </label>
                            <ImageUploader
                                value={data.store_logo}
                                existingPreview={getStorageImageUrl(settings.store_logo)}
                                onChange={({ file, remove }) => {
                                    setData("store_logo", file);
                                    setData("remove_store_logo", remove);
                                }}
                                error={errors.store_logo}
                                imageType="STORE_LOGO"
                                aspectRatio="landscape"
                                maxFileSize={3 * 1024 * 1024}
                                previewSize={240}
                                label="Store logo"
                            />
                        </div>

                        {/* Info */}
                        <div className="lg:flex-1 space-y-4">
                            <Input
                                label="Store Name"
                                value={data.store_name}
                                errors={errors.store_name}
                                onChange={(e) => setData("store_name", e.target.value)}
                                placeholder="Store name"
                            />
                            <Textarea
                                label="Full Address"
                                value={data.store_address}
                                errors={errors.store_address}
                                onChange={(e) => setData("store_address", e.target.value)}
                                rows={3}
                            />
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <Input
                                    label="City/Regency"
                                    value={data.store_city}
                                    errors={errors.store_city}
                                    onChange={(e) => setData("store_city", e.target.value)}
                                    placeholder="contoh: Surabaya"
                                    icon={<IconMapPin size={16} />}
                                />
                                <Input
                                    label="Phone Number"
                                    value={data.store_phone}
                                    errors={errors.store_phone}
                                    onChange={(e) => setData("store_phone", e.target.value)}
                                    placeholder="0812xxxxxxx"
                                    icon={<IconPhone size={16} />}
                                />
                            </div>
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <Input
                                    label="Email"
                                    type="email"
                                    value={data.store_email}
                                    errors={errors.store_email}
                                    onChange={(e) => setData("store_email", e.target.value)}
                                    placeholder="email@toko.com"
                                    icon={<IconMail size={16} />}
                                />
                                <Input
                                    label="Website / Sosial Media"
                                    value={data.store_website}
                                    errors={errors.store_website}
                                    onChange={(e) => setData("store_website", e.target.value)}
                                    placeholder="https://"
                                    icon={<IconWorld size={16} />}
                                />
                            </div>
                        </div>
                    </div>

                    {/* Tax & Legal Section */}
                    <div className="border-t border-slate-100 dark:border-slate-800 pt-6">
                        <h2 className="text-lg font-semibold text-slate-800 dark:text-white mb-4 flex items-center gap-2">
                            <IconReceiptTax size={20} className="text-primary-500" />
                            Tax & Legal Information
                        </h2>
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <Input
                                label="Store NPWP"
                                value={data.store_npwp}
                                errors={errors.store_npwp}
                                onChange={(e) => setData("store_npwp", e.target.value)}
                                placeholder="XX.XXX.XXX.X-XXX.XXX"
                                icon={<IconFileCertificate size={16} />}
                            />
                            <Input
                                label="NIB"
                                value={data.store_nib}
                                errors={errors.store_nib}
                                onChange={(e) => setData("store_nib", e.target.value)}
                                placeholder="Business Identification Number"
                            />
                        </div>
                        <div className="mt-4">
                            <Input
                                label="Default PPN Rate (%)"
                                type="number"
                                step="0.01"
                                min="0"
                                max="100"
                                value={data.tax_default_rate}
                                errors={errors.tax_default_rate}
                                onChange={(e) => setData("tax_default_rate", e.target.value)}
                                placeholder="11.00"
                            />
                            <p className="mt-1 text-xs text-slate-400">
                                Default rate for new products. Can be changed per product.
                            </p>
                        </div>
                    </div>

                    <div className="flex justify-end pt-4 border-t border-slate-100 dark:border-slate-800">
                        <button
                            type="submit"
                            disabled={processing}
                            className="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-primary-500 hover:bg-primary-600 text-white font-medium transition-colors disabled:opacity-50"
                        >
                            <IconDeviceFloppy size={18} />
                            {processing ? "Saving..." : "Save Profile"}
                        </button>
                    </div>
                </form>
            </div>
        </>
    );
}

Store.layout = (page) => <DashboardLayout children={page} />;
