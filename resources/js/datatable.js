import Lara from "@primevue/themes/lara";
import { definePreset } from "@primevue/themes";
const MyPreset = definePreset(Lara, {
    primitive: {
        borderRadius: {
            none: "0",
            xs: "2px",
            sm: "4px",
            md: "6px",
            lg: "8px",
            xl: "12px"
        },
        fontSize: {
            none: "0",
            small: "2px",
            sm: "4px",
            md: "6px",
            lg: "8px",
            xl: "12px"
        }

    },
    semantic: {
        transitionDuration: "0.2s",
        focusRing: {
            width: "0",
            style: "none",
            color: "transparent",
            offset: "0"
        },
        disabledOpacity: "0.6",
        iconSize: "1rem",
        anchorGutter: "2px",
        primary: {
            50: "#f2f2f2",
            100: "#c2c2c2",
            200: "#919191",
            300: "#616161",
            400: "#000000",
            500: "#000000",
            600: "#000000",
            700: "#000000",
            800: "#000000",
            900: "#000000",
            950: "#000000"
        },
        formField: {
            fontSize: "0.875rem",
            paddingX: "0.75rem",
            paddingY: "0.4rem",
            sm: {
                fontSize: "0.875rem",
                paddingX: "0.75rem",
                paddingY: "0.4rem"
            },
            lg: {
                fontSize: "1.125rem",
                paddingX: "0.875rem",
                paddingY: "0.75rem"
            },
            borderRadius: "{border.radius.md}",
            focusRing: {
                width: "{focus.ring.width}",
                style: "{focus.ring.style}",
                color: "{focus.ring.color}",
                offset: "{focus.ring.offset}",
                shadow: "{focus.ring.shadow}"
            },
            transitionDuration: "{transition.duration}"
        },
        list: {
            padding: "0.5rem 0",
            gap: "0",
            header: {
                padding: "0.625rem 1rem 0 1rem"
            },
            option: {
                padding: "0.625rem 1rem",
                borderRadius: "0"
            },
            optionGroup: {
                padding: "0.625rem 1rem",
                fontWeight: "600"
            }
        },
        content: {
            borderRadius: "{border.radius.md}"
        },
        mask: {
            transitionDuration: "0.15s"
        },
        navigation: {
            list: {
                padding: "0.5rem 0",
                gap: "0"
            },
            item: {
                padding: "0.625rem 1rem",
                borderRadius: "0",
                gap: "0.5rem"
            },
            submenuLabel: {
                padding: "0.625rem 1rem",
                fontWeight: "600"
            },
            submenuIcon: {
                size: "0.875rem"
            }
        },
        overlay: {
            select: {
                borderRadius: "{border.radius.md}",
                shadow: "0 2px 12px 0 rgba(0, 0, 0, 0.1)"
            },
            popover: {
                borderRadius: "{border.radius.md}",
                padding: "1rem",
                shadow: "0 1px 3px rgba(0, 0, 0, 0.1)"
            },
            modal: {
                borderRadius: "{border.radius.xl}",
                padding: "1.5rem",
                shadow: "0 1px 3px rgba(0, 0, 0, 0.3)"
            },
            navigation: {
                shadow: "0 2px 12px 0 rgba(0, 0, 0, 0.1)"
            }
        },
        colorScheme: {
            light: {
                surface: {
                    0: "#ffffff",
                    50: "#f8fafc",
                    100: "#f1f5f9",
                    200: "#e2e8f0",
                    300: "#cbd5e1",
                    400: "#94a3b8",
                    500: "#64748b",
                    600: "#475569",
                    700: "#334155",
                    800: "#1e293b",
                    900: "#0f172a",
                    950: "#020617"
                },
                primary: {
                    color: "{primary.500}",
                    contrastColor: "#ffffff",
                    hoverColor: "{primary.600}",
                    activeColor: "{primary.700}"
                },
                highlight: {
                    background: "{primary.50}",
                    focusBackground: "{primary.100}",
                    color: "{primary.700}",
                    focusColor: "{primary.800}"
                },
                focusRing: {
                    shadow: "0 0 0 0.2rem {primary.200}"
                },
                mask: {
                    background: "rgba(0,0,0,0.4)",
                    color: "{surface.200}"
                },
                formField: {
                    background: "{surface.0}",
                    disabledBackground: "{surface.200}",
                    filledBackground: "{surface.50}",
                    filledHoverBackground: "{surface.50}",
                    filledFocusBackground: "{surface.0}",
                    borderColor: "{surface.300}",
                    hoverBorderColor: "{primary.color}",
                    focusBorderColor: "{primary.color}",
                    invalidBorderColor: "{red.400}",
                    color: "{surface.700}",
                    disabledColor: "{surface.500}",
                    placeholderColor: "{surface.500}",
                    invalidPlaceholderColor: "{red.600}",
                    floatLabelColor: "{surface.500}",
                    floatLabelFocusColor: "{primary.600}",
                    floatLabelActiveColor: "{surface.500}",
                    floatLabelInvalidColor: "{form.field.invalid.placeholder.color}",
                    iconColor: "{surface.500}",
                    shadow: "none"
                },
                text: {
                    color: "{surface.700}",
                    hoverColor: "{surface.800}",
                    mutedColor: "{surface.500}",
                    hoverMutedColor: "{surface.600}"
                },
                content: {
                    background: "{surface.0}",
                    hoverBackground: "{surface.100}",
                    borderColor: "{surface.200}",
                    color: "{text.color}",
                    hoverColor: "{text.hover.color}"
                },
                overlay: {
                    select: {
                        background: "{surface.0}",
                        borderColor: "{surface.200}",
                        color: "{text.color}"
                    },
                    popover: {
                        background: "{surface.0}",
                        borderColor: "{surface.200}",
                        color: "{text.color}"
                    },
                    modal: {
                        background: "{surface.0}",
                        borderColor: "{surface.200}",
                        color: "{text.color}"
                    }
                },
                list: {
                    option: {
                        focusBackground: "{surface.100}",
                        selectedBackground: "{highlight.background}",
                        selectedFocusBackground: "{highlight.focus.background}",
                        color: "{text.color}",
                        focusColor: "{text.hover.color}",
                        selectedColor: "{highlight.color}",
                        selectedFocusColor: "{highlight.focus.color}",
                        icon: {
                            color: "{surface.400}",
                            focusColor: "{surface.500}"
                        }
                    },
                    optionGroup: {
                        background: "transparent",
                        color: "{text.color}"
                    }
                },
                navigation: {
                    item: {
                        focusBackground: "{surface.100}",
                        activeBackground: "{surface.100}",
                        color: "{text.color}",
                        focusColor: "{text.hover.color}",
                        activeColor: "{text.hover.color}",
                        icon: {
                            color: "{surface.400}",
                            focusColor: "{surface.500}",
                            activeColor: "{surface.500}"
                        }
                    },
                    submenuLabel: {
                        background: "transparent",
                        color: "{text.color}"
                    },
                    submenuIcon: {
                        color: "{surface.400}",
                        focusColor: "{surface.500}",
                        activeColor: "{surface.500}"
                    }
                }
            },
            dark: {
                surface: {
                    0: "#ffffff",
                    50: "#f9fafb",
                    100: "#f3f4f6",
                    200: "#e5e7eb",
                    300: "#d1d5db",
                    400: "#9ca3af",
                    500: "#6b7280",
                    600: "#4b5563",
                    700: "#374151",
                    900: "#1f2937",
                    800: "#111827",
                    950: "#0f172a",
                },

                primary: {
                    color: "{surface.0}",
                    contrastColor: "{surface.900}",
                    hoverColor: "{surface.200}",
                    activeColor: "{primary.200}"
                },
                highlight: {
                    background: "color-mix(in srgb, {primary.400}, transparent 84%)",
                    focusBackground: "color-mix(in srgb, {primary.400}, transparent 76%)",
                    color: "rgba(255,255,255,.87)",
                    focusColor: "rgba(255,255,255,.87)"
                },
                focusRing: {
                    shadow: "0 0 0 0.2rem color-mix(in srgb, {primary.color}, transparent 80%)"
                },
                mask: {
                    background: "rgba(0,0,0,0.6)",
                    color: "{surface.200}"
                },
                formField: {
                    background: "{surface.950}",
                    disabledBackground: "{surface.700}",
                    filledBackground: "{surface.800}",
                    filledHoverBackground: "{surface.800}",
                    filledFocusBackground: "{surface.950}",
                    borderColor: "{surface.600}",
                    hoverBorderColor: "{primary.color}",
                    focusBorderColor: "{primary.color}",
                    invalidBorderColor: "{red.300}",
                    color: "{surface.0}",
                    disabledColor: "{surface.400}",
                    placeholderColor: "{surface.400}",
                    invalidPlaceholderColor: "{red.400}",
                    floatLabelColor: "{surface.400}",
                    floatLabelFocusColor: "{primary.color}",
                    floatLabelActiveColor: "{surface.400}",
                    floatLabelInvalidColor: "{form.field.invalid.placeholder.color}",
                    iconColor: "{surface.400}",
                    shadow: "none"
                },
                text: {
                    color: "{surface.0}",
                    hoverColor: "{surface.0}",
                    mutedColor: "{surface.400}",
                    hoverMutedColor: "{surface.300}"
                },
                content: {
                    background: "{surface.900}",
                    hoverBackground: "{surface.800}",
                    borderColor: "{surface.700}",
                    color: "{text.color}",
                    hoverColor: "{text.hover.color}"
                },
                overlay: {
                    select: {
                        background: "{surface.900}",
                        borderColor: "{surface.700}",
                        color: "{text.color}"
                    },
                    popover: {
                        background: "{surface.900}",
                        borderColor: "{surface.700}",
                        color: "{text.color}"
                    },
                    modal: {
                        background: "{surface.900}",
                        borderColor: "{surface.700}",
                        color: "{text.color}"
                    }
                },
                list: {
                    option: {
                        focusBackground: "{surface.800}",
                        selectedBackground: "{highlight.background}",
                        selectedFocusBackground: "{highlight.focus.background}",
                        color: "{text.color}",
                        focusColor: "{text.hover.color}",
                        selectedColor: "{highlight.color}",
                        selectedFocusColor: "{highlight.focus.color}",
                        icon: {
                            color: "{surface.500}",
                            focusColor: "{surface.400}"
                        }
                    },
                    optionGroup: {
                        background: "transparent",
                        color: "{text.color}"
                    }
                },
                navigation: {
                    item: {
                        focusBackground: "{surface.800}",
                        activeBackground: "{surface.800}",
                        color: "{text.color}",
                        focusColor: "{text.hover.color}",
                        activeColor: "{text.hover.color}",
                        icon: {
                            color: "{surface.500}",
                            focusColor: "{surface.400}",
                            activeColor: "{surface.400}"
                        }
                    },
                    submenuLabel: {
                        background: "transparent",
                        color: "{text.color}"
                    },
                    submenuIcon: {
                        color: "{surface.500}",
                        focusColor: "{surface.400}",
                        activeColor: "{surface.400}"
                    }
                }
            }
        }
    }
});

export default MyPreset;
