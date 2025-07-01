export default function AppLogo() {
    return (
        <>
            <div className="flex aspect-square size-8 items-center justify-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground">
                <img
                    src="/logo.png"
                    alt="Rachmat Logo"
                    className="size-6 object-contain"
                    loading="lazy"
                />
            </div>
            <div className="ml-1 grid flex-1 text-sm">
                <span className="mb-0.5 truncate leading-tight font-semibold">رشمات</span>
            </div>
        </>
    );
}
