// @ts-ignore
const modules = import.meta.glob(
    "../../Modules/**/resources/assets/ts/**/*.ts",
    {
        eager: true, // automatically run side effects
    },
);

// If your modules export a default function, invoke it:
Object.values(modules).forEach((mod: any) => {
    if (typeof mod.default === "function") {
        mod.default();
    }
});
