let Ī = {};
Ī.y = Ī => {
    const {
        fonts
    } = Ī.z(Ī);
    let dictionary;
    return {
        dictionary: {
            accent: {
                color: {
                    _: {
                        type: {
                            color: true
                        }
                    }
                }
            },
            align: {
                content: {
                    _: {
                        values: {
                            stretch: true,
                            center: true,
                            "flex-start": true,
                            "flex-end": true,
                            "space-between": true,
                            "space-around": true,
                            "space-evenly": true,
                            initial: true,
                            unset: true
                        }
                    }
                },
                items: {
                    _: {
                        ref: "$up.content"
                    }
                },
                self: {
                    _: {
                        ref: "$up.content"
                    }
                }
            },
            all: {
                _: {}
            },
            animation: {
                _: {
                    combine: ["name", "duration", "timing.function", "delay", "iteration.count", "direction", "fill.mode", "play.state"],
                    multi: true,
                    seperated: true,
                    max: 5
                },
                delay: {
                    _: {
                        type: {
                            time: true
                        }
                    }
                },
                direction: {
                    _: {
                        values: {
                            normal: true,
                            reverse: true,
                            alternate: true,
                            "alternate-reverse": true
                        },
                        seperated: true
                    }
                },
                duration: {
                    _: {
                        type: {
                            time: true
                        }
                    }
                },
                fill: {
                    mode: {
                        _: {
                            values: {
                                none: true,
                                forwards: true,
                                backwords: true,
                                both: true
                            },
                            seperated: true
                        }
                    }
                },
                iteration: {
                    count: {
                        _: {
                            type: {
                                number: true,
                                custom: true
                            },
                            values: {
                                infinite: true
                            },
                            seperated: true
                        }
                    }
                },
                name: {
                    _: {
                        type: {
                            name: true,
                            custom: true
                        },
                        values: {
                            none: true
                        },
                        seperated: true
                    }
                },
                play: {
                    state: {
                        _: {
                            values: {
                                running: true,
                                paused: true
                            },
                            multi: true
                        }
                    }
                },
                timing: {
                    function: {
                        _: {
                            values: {
                                ease: true,
                                "ease-in": true,
                                "ease-out": true,
                                "ease-in-out": true,
                                linear: true,
                                "step-start": true,
                                "step-end": true
                            },
                            multi: true
                        }
                    }
                }
            },
            appearance: {
                _: {
                    values: {
                        auto: true,
                        textfield: true,
                        "menulist-button": true
                    }
                }
            },
            aspect: {
                ratio: {
                    _: {
                        type: {
                            number: true
                        },
                        seperated: true,
                        seperator: {
                            '/': true
                        }
                    }
                }
            },
            backdrop: {
                filter: {
                    _: {
                        type: {
                            functions: true,
                            custom: true
                        },
                        values: {
                            none: true
                        },
                        functions: {
                            url: true,
                            blur: true,
                            brightness: true,
                            contrast: true,
                            "drop-shadow": true,
                            grayscale: true,
                            "hue-rotate": true,
                            invert: true,
                            opacity: true,
                            sepia: true,
                            saturate: true
                        },
                        multi: true,
                        max: 11
                    }
                }
            },
            backface: {
                visibility: {
                    _: {
                        values: {
                            visible: true,
                            hidden: true
                        }
                    }
                }
            },
            background: {
                _: {
                    combine: ["attachment", "clip", "color", "image", "origin", "position", "repeat", "size"],
                    multi: true,
                    max: 7
                },
                attachment: {
                    _: {
                        values: {
                            scroll: true,
                            fixed: true,
                            local: true
                        }
                    }
                },
                blend: {
                    mode: {
                        _: {
                            values: {
                                normal: true,
                                multiply: true,
                                screen: true,
                                overlay: true,
                                darken: true,
                                lighten: true,
                                "color-dodge": true,
                                "color-saturation": true,
                                color: true,
                                saturation: true,
                                luminosity: true
                            },
                            seperated: true
                        }
                    }
                },
                clip: {
                    _: {
                        values: {
                            "border-box": true,
                            "padding-box": true,
                            "content-box": true,
                            text: true
                        }
                    }
                },
                color: {
                    _: {
                        type: {
                            color: true,
                            custom: true
                        },
                        values: {
                            transparent: true,
                            currentColor: true
                        }
                    }
                },
                image: {
                    _: {
                        type: {
                            image: true,
                            custom: true
                        },
                        values: {
                            none: true
                        }
                    }
                },
                origin: {
                    _: {
                        values: {
                            "border-box": true,
                            "padding-box": true,
                            "content-box": true
                        }
                    }
                },
                position: {
                    _: {
                        type: {
                            length: true,
                            procent: true,
                            custom: true
                        },
                        values: {
                            left: true,
                            right: true,
                            bottom: true,
                            center: true,
                            top: true
                        },
                        separated: true,
                        multi: true,
                        max: 4
                    },
                    x: {
                        _: {
                            type: {
                                custom: true,
                                length: true
                            },
                            values: {
                                left: true,
                                center: true,
                                right: true
                            },
                            separated: true
                        }
                    },
                    y: {
                        _: {
                            type: {
                                custom: true,
                                length: true
                            },
                            values: {
                                top: true,
                                center: true,
                                bottom: true
                            },
                            separated: true
                        }
                    }
                },
                repeat: {
                    _: {
                        values: {
                            "repeat-x": true,
                            "repeat-y": true,
                            repeat: true,
                            space: true,
                            round: true,
                            "no-repeat": true
                        },
                        multi: true
                    }
                },
                size: {
                    _: {
                        values: {
                            auto: true,
                            cover: true,
                            contain: true
                        },
                        type: {
                            length: true,
                            procent: true,
                            custom: true
                        },
                        multi: true,
                        max: 2,
                        seperated: true
                    }
                }
            },
            block: {
                size: {
                    _: {
                        values: {
                            auto: true,
                            "fit-content": true,
                            "min-content": true,
                            "max-content": true,
                            "border-box": true,
                            available: true
                        },
                        type: {
                            length: true,
                            custom: true
                        },
                        separated: true
                    }
                }
            },
            border: {
                _: {
                    combine: ["bottom.style", "bottom.width", "bottom.color"],
                    multi: true,
                    max: 3
                },
                color: {
                    _: {
                        type: {
                            color: true
                        }
                    }
                },
                block: {
                    color: {
                        _: {
                            ref: "$up.end.color"
                        }
                    },
                    end: {
                        _: {
                            combine: ["color", "style", "width"],
                            multi: true,
                            max: 3
                        },
                        color: {
                            _: {
                                type: {
                                    color: true
                                }
                            }
                        },
                        style: {
                            _: {
                                values: {
                                    dashed: true,
                                    dotted: true,
                                    groove: true
                                }
                            }
                        },
                        width: {
                            _: {
                                values: {
                                    thick: true
                                },
                                type: {
                                    length: true,
                                    custom: true
                                }
                            }
                        }
                    },
                    start: {
                        _: {
                            combine: ["color", "style", "width"],
                            multi: true,
                            max: 3
                        },
                        color: {
                            _: {
                                ref: "$up.$up.end.color"
                            }
                        },
                        style: {
                            _: {
                                ref: "$up.$up.end.style"
                            }
                        },
                        width: {
                            _: {
                                ref: "$up.$up.end.width"
                            }
                        }
                    },
                    width: {
                        _: {
                            values: {
                                thick: true
                            },
                            type: {
                                length: true,
                                custom: true
                            }
                        }
                    }
                },
                bottom: {
                    _: {
                        combine: ["style", "color", "width"],
                        multi: true,
                        max: 3
                    },
                    color: {
                        _: {
                            values: {
                                transparent: true
                            },
                            type: {
                                custom: true,
                                color: true
                            }
                        }
                    },
                    left: {
                        radius: {
                            _: {
                                type: {
                                    length: true,
                                    procent: true
                                }
                            }
                        }
                    },
                    right: {
                        radius: {
                            _: {
                                ref: "$up.$up.left.radius"
                            }
                        }
                    },
                    style: {
                        _: {
                            values: {
                                none: true,
                                dashed: true,
                                dotted: true,
                                solid: true,
                                hidden: true,
                                double: true
                            },
                            multi: true,
                            max: 4
                        }
                    },
                    width: {
                        _: {
                            type: {
                                length: true
                            },
                            multi: true,
                            max: 4
                        }
                    }
                },
                collapse: {
                    _: {
                        values: {
                            collapse: true,
                            separate: true
                        }
                    }
                },
                end: {
                    end: {
                        radius: {
                            _: {
                                type: {
                                    length: true
                                }
                            }
                        }
                    },
                    start: {
                        radius: {
                            _: {
                                ref: "$up.$up.end.radius"
                            }
                        }
                    }
                },
                image: {
                    _: {
                        type: {
                            image: true
                        }
                    },
                    outset: {
                        _: {
                            type: {
                                length: true
                            },
                            multi: true
                        }
                    },
                    repeat: {
                        _: {
                            values: {
                                stretch: true,
                                repeat: true,
                                round: true,
                                space: true
                            }
                        }
                    },
                    slice: {
                        _: {
                            type: {
                                custom: true,
                                length: true,
                                procent: true
                            },
                            values: {
                                fill: true
                            }
                        }
                    },
                    source: {
                        _: {
                            type: {
                                image: true
                            }
                        }
                    },
                    width: {
                        _: {
                            values: {
                                auto: true
                            },
                            type: {
                                custom: true,
                                length: true,
                                procent: true
                            }
                        }
                    }
                },
                inline: {
                    _: {
                        combine: ["color", "style", "width"],
                        multi: true,
                        max: 3
                    },
                    color: {
                        _: {
                            type: {
                                color: true
                            }
                        }
                    },
                    end: {
                        _: {
                            combine: ["color", "style", "width"],
                            multi: true,
                            max: 3
                        },
                        color: {
                            _: {
                                type: {
                                    color: true
                                }
                            }
                        },
                        style: {
                            _: {
                                values: {
                                    none: true,
                                    dashed: true,
                                    dotted: true,
                                    solid: true,
                                    hidden: true,
                                    double: true
                                },
                                multi: true
                            }
                        },
                        width: {
                            _: {
                                values: {
                                    thick: true
                                },
                                type: {
                                    length: true,
                                    custom: true
                                }
                            }
                        }
                    },
                    start: {
                        _: {
                            combine: ["color", "style", "width"],
                            multi: true,
                            max: 3
                        },
                        color: {
                            _: {
                                ref: "$up.$up.end.color"
                            }
                        },
                        style: {
                            _: {
                                ref: "$up.$up.end.style"
                            }
                        },
                        width: {
                            _: {
                                ref: "$up.$up.end.width"
                            }
                        }
                    },
                    style: {
                        _: {
                            ref: "$up.end.style"
                        }
                    },
                    width: {
                        _: {
                            ref: "$up.end.width"
                        }
                    }
                },
                left: {
                    _: {
                        combine: ["color", "style", "width"],
                        multi: true,
                        max: 3
                    },
                    color: {
                        _: {
                            ref: "$up.$up.bottom.color"
                        }
                    },
                    style: {
                        _: {
                            ref: "$up.$up.bottom.style"
                        }
                    },
                    width: {
                        _: {
                            ref: "$up.$up.bottom.width"
                        }
                    }
                },
                right: {
                    _: {
                        ref: "$up.left",
                        multi: true,
                        max: 3
                    },
                    color: {
                        _: {
                            ref: "$up.$up.bottom.color"
                        }
                    },
                    style: {
                        _: {
                            ref: "$up.$up.bottom.style"
                        }
                    },
                    width: {
                        _: {
                            ref: "$up.$up.bottom.width"
                        }
                    }
                },
                spacing: {
                    _: {
                        type: {
                            length: true
                        },
                        separated: true
                    }
                },
                start: {
                    end: {
                        radius: {
                            _: {
                                ref: "$up.$up.$up.end.end.radius"
                            }
                        }
                    },
                    start: {
                        radius: {
                            _: {
                                ref: "$up.$up.end.radius"
                            }
                        }
                    }
                },
                top: {
                    _: {
                        ref: "$up.$up.left"
                    },
                    color: {
                        _: {
                            ref: "$up.$up.bottom.color"
                        }
                    },
                    left: {
                        radius: {
                            _: {
                                ref: "$up.$up.$up.bottom.left.radius"
                            }
                        }
                    },
                    right: {
                        radius: {
                            _: {
                                ref: "$up.$up.left.radius"
                            }
                        }
                    },
                    style: {
                        _: {
                            ref: "$up.$up.bottom.style"
                        }
                    },
                    width: {
                        _: {
                            ref: "$up.$up.bottom.width"
                        }
                    }
                },
                style: {
                    _: {
                        ref: "$up.bottom.style"
                    }
                },
                width: {
                    _: {
                        ref: "$up.bottom.width"
                    }
                },
                radius: {
                    _: {
                        type: {
                            procent: true,
                            length: true
                        },
                        multi: true,
                        max: 4
                    }
                }
            },
            bottom: {
                _: {
                    values: {
                        auto: true
                    },
                    type: {
                        length: true,
                        procent: true,
                        custom: true
                    }
                }
            },
            box: {
                decoration: {
                    break: {
                        _: {
                            values: {
                                slice: true,
                                clone: true
                            }
                        }
                    }
                },
                shadow: {
                    _: {
                        values: {
                            inset: true
                        },
                        type: {
                            color: true,
                            custom: true
                        },
                        multi: true,
                        separated: true
                    }
                },
                sizing: {
                    _: {
                        values: {
                            "content-box": true,
                            "border-box": true
                        }
                    }
                }
            },
            break: {
                after: {
                    _: {
                        values: {
                            auto: true,
                            always: true,
                            left: true,
                            right: true,
                            recto: true,
                            verso: true,
                            page: true,
                            column: true,
                            region: true,
                            avoid: true,
                            "avoid-page": true,
                            "avoid-column": true,
                            "avoid-region": true
                        }
                    }
                },
                before: {
                    _: {
                        ref: "$up.after"
                    }
                },
                inside: {
                    _: {
                        values: {
                            auto: true,
                            avoid: true,
                            "avoid-page": true,
                            "avoid-column": true,
                            "avoid-region": true
                        }
                    }
                }
            },
            caption: {
                side: {
                    _: {
                        values: {
                            top: true,
                            bottom: true,
                            left: true,
                            right: true,
                            "top-outside": true,
                            "bottom-outside": true
                        }
                    }
                }
            },
            caret: {
                color: {
                    _: {
                        values: {
                            auto: true
                        },
                        type: {
                            color: true,
                            custom: true
                        }
                    }
                }
            },
            clear: {
                _: {
                    values: {
                        none: true,
                        left: true,
                        right: true,
                        both: true,
                        "inline-start": true,
                        "inline-end": true
                    }
                }
            },
            clip: {
                _: {
                    values: {
                        auto: true
                    },
                    type: {
                        custom: true,
                        functions: true
                    },
                    functions: {
                        rect: true
                    }
                },
                path: {
                    _: {
                        values: {
                            none: true,
                            "fill-box": true,
                            "stroke-box": true,
                            "view-box": true,
                            "margin-box": true,
                            "border-box": true,
                            "padding-box": true,
                            "content-box": true
                        },
                        type: {
                            image: true,
                            custom: true,
                            functions: true
                        },
                        functions: {
                            inset: true,
                            circle: true,
                            ellipse: true,
                            polygon: true,
                            path: true,
                            url: true
                        },
                        separated: true
                    }
                },
                rule: {
                    _: {
                        values: {
                            nonzero: true,
                            evenodd: true
                        }
                    }
                }
            },
            color: {
                _: {
                    type: {
                        color: true
                    }
                },
                scheme: {
                    _: {
                        type: {
                            custom: true
                        },
                        values: {
                            normal: true,
                            light: true,
                            dark: true
                        },
                        multi: true,
                        max: 2
                    }
                },
                adjust: {
                    _: {
                        values: {
                            economy: true,
                            exact: true
                        }
                    }
                },
                interpolation: {
                    _: {
                        values: {
                            auto: true,
                            sRGB: true,
                            linearRGB: true
                        }
                    },
                    filters: {
                        _: {
                            values: {
                                auto: true,
                                sRGB: true,
                                linearRGB: true
                            }
                        }
                    }
                }
            },
            column: {
                count: {
                    _: {
                        values: {
                            auto: true
                        },
                        type: {
                            number: true,
                            custom: true
                        }
                    }
                },
                fill: {
                    _: {
                        values: {
                            auto: true,
                            balance: true,
                            "balance-all": true
                        }
                    }
                },
                gap: {
                    _: {
                        values: {
                            normal: true
                        },
                        type: {
                            custom: true,
                            length: true,
                            procent: true
                        }
                    }
                },
                rule: {
                    _: {
                        combine: ["color", "style", "width"],
                        multi: true,
                        max: 3
                    },
                    color: {
                        _: {
                            type: {
                                color: true
                            }
                        }
                    },
                    style: {
                        _: {
                            values: {
                                none: true,
                                hidden: true,
                                dotted: true,
                                dashed: true,
                                solid: true,
                                double: true,
                                groove: true,
                                ridge: true,
                                inset: true,
                                outset: true
                            }
                        }
                    },
                    width: {
                        _: {
                            values: {
                                thin: true,
                                medium: true,
                                thick: true
                            },
                            type: {
                                custom: true,
                                length: true
                            }
                        }
                    }
                },
                span: {
                    _: {
                        values: {
                            none: true,
                            all: true
                        }
                    }
                },
                width: {
                    _: {
                        values: {
                            auto: true
                        },
                        type: {
                            custom: true,
                            length: true
                        }
                    }
                }
            },
            columns: {
                _: {
                    combine: ["$up.column.count", "$up.column.width"],
                    multi: true,
                    max: 2
                }
            },
            contain: {
                _: {
                    values: {
                        none: true,
                        strict: true,
                        content: true,
                        size: true,
                        layout: true,
                        style: true,
                        paint: true
                    },
                    separated: true
                }
            },
            content: {
                _: {
                    values: {
                        normal: true,
                        none: true,
                        "open-quote": true,
                        "close-quote": true,
                        "no-open-quote": true,
                        "no-close-quote": true
                    },
                    type: {
                        custom: true,
                        name: true,
                        image: true,
                        functions: true
                    },
                    functions: {
                        attr: true,
                        counter: true
                    },
                    separated: true
                },
                visibility: {
                    _: {
                        type: {
                            custom: true
                        },
                        values: {
                            visible: true,
                            hidden: true,
                            auto: true
                        }
                    }
                }
            },
            counter: {
                increment: {
                    _: {
                        values: {
                            none: true
                        },
                        type: {
                            number: true,
                            name: true
                        },
                        separated: true
                    }
                },
                reset: {
                    _: {
                        ref: "$up.increment"
                    }
                },
                set: {
                    _: {
                        ref: "$up.increment"
                    }
                }
            },
            cursor: {
                _: {
                    values: {
                        alias: true,
                        "all-scroll": true,
                        auto: true,
                        cell: true,
                        "context-menu": true,
                        "col-resize": true,
                        copy: true,
                        crosshair: true,
                        default: true,
                        "e-resize": true,
                        "ew-resize": true,
                        grab: true,
                        grabbing: true,
                        help: true,
                        move: true,
                        "n-resize": true,
                        "ne-resize": true,
                        "nesw-resize": true,
                        "ns-resize": true,
                        "nw-resize": true,
                        "nwse-resize": true,
                        "no-drop": true,
                        none: true,
                        "not-allowed": true,
                        pointer: true,
                        progress: true,
                        "row-resize": true,
                        "s-resize": true,
                        "se-resize": true,
                        "sw-resize": true,
                        text: true,
                        "w-resize": true,
                        wait: true,
                        "zoom-in": true,
                        "zoom-out": true
                    },
                    type: {
                        custom: true,
                        image: true
                    }
                }
            },
            cx: {
                _: {
                    type: {
                        number: true
                    }
                }
            },
            cy: {
                _: {
                    ref: "$up.$up.cx"
                }
            },
            d: {
                _: {
                    values: {
                        A: true,
                        a: true,
                        M: true,
                        m: true,
                        L: true,
                        l: true,
                        H: true,
                        h: true,
                        V: true,
                        v: true,
                        C: true,
                        c: true,
                        S: true,
                        s: true,
                        Z: true,
                        z: true,
                        Q: true,
                        q: true,
                        T: true,
                        t: true
                    },
                    type: {
                        custom: true,
                        number: true
                    },
                    multi: true,
                    max: 3,
                    seperated: true
                }
            },
            direction: {
                _: {
                    values: {
                        ltr: true,
                        rtl: true
                    }
                }
            },
            display: {
                _: {
                    values: {
                        block: true,
                        inline: true,
                        "inline-block": true,
                        flex: true,
                        "inline-flex": true,
                        grid: true,
                        "inline-grid": true,
                        "flow-root": true,
                        table: true,
                        "table-row": true,
                        "list-item": true,
                        "inline-table": true
                    },
                    separated: true
                }
            },
            dominant: {
                baseline: {
                    _: {
                        values: {
                            auto: true,
                            middle: true,
                            hanging: true
                        }
                    }
                }
            },
            empty: {
                cells: {
                    _: {
                        values: {
                            show: true,
                            hide: true
                        }
                    }
                }
            },
            fill: {
                _: {
                    type: {
                        color: true
                    }
                },
                opacity: {
                    _: {
                        type: {
                            number: true
                        }
                    }
                },
                rule: {
                    _: {
                        values: {
                            evenodd: true,
                            nonzero: true
                        }
                    }
                }
            },
            filter: {
                _: {
                    type: {
                        functions: true
                    },
                    functions: {
                        url: "string",
                        blur: "length",
                        brightness: "number",
                        contrast: "procent",
                        "drop-shadow": "shadow",
                        greyscale: "procent",
                        "hue-rotate": "degree",
                        invert: "procent",
                        opacity: "procent"
                    }
                }
            },
            flex: {
                _: {
                    combine: ["grow", "shrink", "basis"],
                    multi: true,
                    max: 3
                },
                flow: {
                    _: {
                        combine: ["direction", "wrap"],
                        multi: true,
                        max: 2
                    }
                },
                basis: {
                    _: {
                        values: {
                            auto: true,
                            fill: true,
                            "max-content": true,
                            "min-content": true,
                            "fit-content": true,
                            content: true
                        },
                        type: {
                            custom: true,
                            length: true
                        }
                    }
                },
                direction: {
                    _: {
                        values: {
                            row: true,
                            "row-reverse": true,
                            column: true,
                            "cloumn-reverse": true
                        }
                    }
                },
                grow: {
                    _: {
                        type: {
                            number: true
                        }
                    }
                },
                shrink: {
                    _: {
                        type: {
                            number: true
                        }
                    }
                },
                wrap: {
                    _: {
                        values: {
                            nowrap: true,
                            wrap: true,
                            "wrap-reverse": true
                        }
                    }
                }
            },
            float: {
                _: {
                    values: {
                        left: true,
                        right: true,
                        none: true,
                        "inline-start": true,
                        "inline-end": true
                    }
                }
            },
            flood: {
                color: {
                    _: {
                        type: {
                            color: true
                        }
                    }
                },
                opacity: {
                    _: {
                        type: {
                            number: true
                        }
                    }
                }
            },
            font: {
                _: {
                    combine: ["family", "size", "stretch", "style", "variant", "weight", "$up.line.height"],
                    multi: true,
                    max: 6
                },
                family: {
                    _: {
                        values: fonts,
                        type: {
                            custom: true,
                            name: true
                        },
                        seperated: true
                    }
                },
                feature: {
                    settings: {
                        _: {
                            values: {
                                liga: true,
                                dlig: true,
                                onum: true,
                                lnum: true,
                                tnum: true,
                                zero: true,
                                frac: true,
                                sups: true,
                                subs: true,
                                smpc: true,
                                c2sc: true,
                                case: true,
                                hlig: true,
                                calt: true,
                                swsh: true,
                                hist: true,
                                "ss**": true,
                                kern: true,
                                locl: true,
                                rlig: true,
                                medi: true,
                                init: true,
                                isol: true,
                                fina: true,
                                mark: true,
                                mkmk: true
                            },
                            type: {
                                custom: true,
                                number: true
                            },
                            multi: true,
                            separated: true
                        }
                    }
                },
                kerning: {
                    _: {
                        values: {
                            auto: true,
                            normal: true,
                            none: true
                        }
                    }
                },
                language: {
                    override: {
                        _: {
                            values: {
                                none: true
                            },
                            type: {
                                custom: true
                            }
                        }
                    }
                },
                optical: {
                    sizing: {
                        _: {
                            values: {
                                auto: true,
                                none: true
                            }
                        }
                    }
                },
                size: {
                    _: {
                        values: {
                            "xx-small": true,
                            "x-small": true,
                            small: true,
                            medium: true,
                            large: true,
                            "x-large": true,
                            "xx-large": true,
                            larger: true,
                            smaller: true
                        },
                        type: {
                            custom: true,
                            length: true,
                            procent: true
                        }
                    },
                    adjust: {
                        _: {
                            values: {
                                none: true
                            },
                            type: {
                                custom: true,
                                number: true
                            }
                        }
                    }
                },
                stretch: {
                    _: {
                        values: {
                            normal: true,
                            "semi-condensed": true,
                            condensed: true,
                            "extra-condensed": true,
                            "ultra-condensed": true,
                            "semi-expanded": true,
                            expanded: true,
                            "extra-expanded": true,
                            "ultra-expanded": true
                        },
                        type: {
                            custom: true,
                            procent: true
                        }
                    }
                },
                style: {
                    _: {
                        values: {
                            normal: true,
                            italic: true,
                            oblique: true
                        },
                        type: {
                            custom: true,
                            degree: true
                        },
                        separated: true
                    }
                },
                synthesis: {
                    _: {
                        values: {
                            none: true,
                            weight: true,
                            style: true
                        },
                        separated: true
                    }
                },
                variant: {
                    _: {
                        values: {
                            normal: true,
                            none: true,
                            "common-ligatures": true,
                            "no-common-ligatures": true,
                            "discretionary-ligatures": true,
                            "no-discretionary-ligatures": true,
                            "historical-ligatures": true,
                            "no-historical-ligatures": true,
                            contextual: true,
                            "no-contextual": true,
                            "historical-forms": true,
                            "small-caps": true,
                            "all-small-caps": true,
                            "petite-caps": true,
                            "all-petite-caps": true,
                            unicase: true,
                            "titling-caps": true,
                            "lining-nums": true,
                            "oldstyle-nums": true,
                            "proportional-nums": true,
                            "tabular-nums": true,
                            "diagonal-fractions": true,
                            "stacked-fractions": true,
                            ordinal: true,
                            "slashed-zero": true,
                            jis78: true,
                            jis83: true,
                            jis90: true,
                            jis04: true,
                            simplified: true,
                            traditional: true,
                            "full-width": true,
                            "proportional-width": true,
                            ruby: true
                        },
                        functions: {
                            stylistic: true,
                            styleset: true,
                            "character-variant": true,
                            swash: true,
                            ornaments: true,
                            annotation: true
                        },
                        type: {
                            custom: true,
                            functions: true
                        },
                        separated: true
                    },
                    alternates: {
                        _: {
                            values: {
                                normal: true,
                                "historical-forms": true
                            },
                            functions: {
                                stylistic: true,
                                styleset: true,
                                "character-variant": true,
                                swash: true,
                                ornaments: true,
                                annotation: true
                            },
                            type: {
                                custom: true,
                                functions: true
                            },
                            separated: true
                        }
                    },
                    caps: {
                        _: {
                            values: {
                                normal: true,
                                "small-caps": true,
                                "all-small-caps": true,
                                "petite-caps": true,
                                "all-petite-caps": true,
                                unicase: true,
                                "titling-caps": true
                            }
                        }
                    },
                    east: {
                        asian: {
                            _: {
                                values: {
                                    jis78: true,
                                    jis83: true,
                                    jis90: true,
                                    jis04: true,
                                    simplified: true,
                                    traditional: true,
                                    "full-width": true,
                                    "proportional-width": true,
                                    ruby: true
                                },
                                separated: true
                            }
                        }
                    },
                    ligatures: {
                        _: {
                            values: {
                                normal: true,
                                none: true,
                                "common-ligatures": true,
                                "no-common-ligatures": true,
                                "discretionary-ligatures": true,
                                "no-discretionary-ligatures": true,
                                "historical-ligatures": true,
                                "no-historical-ligatures": true,
                                contextual: true,
                                "no-contextual": true
                            }
                        }
                    },
                    numeric: {
                        _: {
                            values: {
                                normal: true,
                                "lining-nums": true,
                                "oldstyle-nums": true,
                                "proportional-nums": true,
                                "tabular-nums": true,
                                "diagonal-fractions": true,
                                "stacked-fractions": true,
                                ordinal: true,
                                "slashed-zero": true
                            },
                            separated: true
                        }
                    },
                    position: {
                        _: {
                            values: {
                                normal: true,
                                sub: true,
                                super: true
                            }
                        }
                    }
                },
                variation: {
                    settings: {
                        _: {
                            values: {
                                normal: true,
                                wght: true,
                                wdth: true,
                                slnt: true,
                                ital: true,
                                opsz: true
                            },
                            type: {
                                custom: true,
                                number: true
                            },
                            separated: true
                        }
                    }
                },
                weight: {
                    _: {
                        values: {
                            normal: true,
                            bold: true,
                            lighter: true,
                            bolder: true
                        },
                        type: {
                            custom: true,
                            number: true
                        }
                    }
                }
            },
            forced: {
                color: {
                    adjust: {
                        _: {
                            type: {
                                custom: true
                            },
                            values: {
                                auto: true,
                                none: true
                            }
                        }
                    }
                }
            },
            gap: {
                _: {
                    type: {
                        length: true,
                        procent: true
                    },
                    multi: true,
                    max: 2
                }
            },
            grid: {
                _: {
                    combine: ["auto.columns", "auto.flow", "auto.rows", "template.areas", "template.columns", "template.rows"],
                    multi: true
                },
                gap: {
                    _: {
                        ref: "$up.$up.gap"
                    }
                },
                area: {
                    _: {
                        combine: ["$up.row.start", "$up.column.start", "$up.row.end", "$up.column.end"],
                        seperated: true,
                        seperator: {
                            '/': true
                        }
                    }
                },
                auto: {
                    columns: {
                        _: {
                            values: {
                                auto: true,
                                "min-content": true,
                                "max-content": true
                            },
                            type: {
                                length: true,
                                procent: true,
                                fraction: true,
                                functions: true
                            },
                            functions: {
                                minmax: true,
                                "fit-content": true
                            },
                            separated: true
                        }
                    },
                    flow: {
                        _: {
                            values: {
                                row: true,
                                column: true,
                                dense: true
                            },
                            separated: true
                        }
                    },
                    rows: {
                        _: {
                            ref: "$up.columns"
                        }
                    }
                },
                column: {
                    _: {
                        combin: ["end", "start"],
                        seperated: true,
                        seperator: {
                            '/': true
                        }
                    },
                    gap: {
                        _: {
                            ref: "$up.$up.$up.column.gap"
                        }
                    },
                    end: {
                        _: {
                            values: {
                                auto: true,
                                span: true
                            },
                            type: {
                                custom: true,
                                number: true,
                                name: true
                            },
                            multi: true,
                            max: 3
                        }
                    },
                    start: {
                        _: {
                            ref: "$up.end"
                        }
                    }
                },
                row: {
                    _: {
                        combine: ["end", "start"],
                        seperated: true,
                        seperator: {
                            '/': true
                        }
                    },
                    gap: {
                        _: {
                            ref: "$up.$up.$up.row.gap"
                        }
                    },
                    end: {
                        _: {
                            ref: "$up.$up.column.end"
                        }
                    },
                    start: {
                        _: {
                            ref: "$up.$up.column.end"
                        }
                    }
                },
                template: {
                    _: {
                        combine: ["areas", "columns", "rows"],
                        multi: true
                    },
                    areas: {
                        _: {
                            multi: true
                        }
                    },
                    columns: {
                        _: {
                            custom: {
                                "min-content": true,
                                "max-content": true
                            },
                            type: {
                                custom: true,
                                fraction: true,
                                length: true,
                                procent: true,
                                functions: true,
                                name: true
                            },
                            functions: {
                                minmax: true,
                                "fit-content": true,
                                repeat: true,
                                subgrid: true,
                                masonery: true
                            },
                            multi: true
                        }
                    },
                    rows: {
                        _: {
                            ref: "$up.columns"
                        }
                    }
                }
            },
            hanging: {
                punctuation: {
                    _: {
                        type: {
                            custom: true
                        },
                        values: {
                            none: true,
                            first: true,
                            last: true,
                            "force-end": true,
                            "allow-end": true
                        },
                        multi: true,
                        max: 3
                    }
                }
            },
            height: {
                _: {
                    values: {
                        auto: true,
                        "max-content": true,
                        "min-content": true
                    },
                    type: {
                        custom: true,
                        length: true,
                        procent: true,
                        functions: true
                    },
                    functions: {
                        "fit-content": true
                    }
                }
            },
            hyphenate: {
                character: {
                    _: {
                        type: {
                            custom: true,
                            string: true
                        },
                        values: {
                            auto: true
                        }
                    }
                }
            },
            hyphens: {
                _: {
                    values: {
                        none: true,
                        manual: true,
                        auto: true
                    }
                }
            },
            image: {
                orientation: {
                    _: {
                        depricated: true
                    }
                },
                rendering: {
                    _: {
                        values: {
                            auto: true,
                            "crisp-edges": true,
                            pixelated: true,
                            smooth: true,
                            "high-quality": true,
                            : true
                        }
                    }
                },
                resolution: {
                    _: {
                        type: {
                            custom: true,
                            resolution: true
                        },
                        values: {
                            "from-image": true,
                            snap: true
                        }
                    }
                }
            },
            ime: {
                mode: {
                    _: {
                        depricated: true
                    }
                }
            },
            initial: {
                letter: {
                    _: {
                        type: {
                            custom: true,
                            number: true
                        },
                        values: {
                            normal: true
                        },
                        multi: true,
                        max: 2
                    },
                    align: {
                        _: {
                            values: {
                                auto: true,
                                alphabetic: true,
                                hanging: true,
                                ideographic: true
                            }
                        }
                    }
                }
            },
            inline: {
                size: {
                    _: {
                        ref: "$up.$up.width"
                    }
                }
            },
            inset: {
                _: {
                    type: {
                        length: true
                    },
                    multi: true,
                    max: 4,
                    values: {
                        auto: true
                    }
                },
                block: {
                    _: {
                        combine: ["end", "start"],
                        multi: true,
                        max: 2
                    },
                    end: {
                        _: {
                            values: {
                                auto: true
                            },
                            type: {
                                custom: true,
                                length: true,
                                procent: true
                            }
                        }
                    },
                    start: {
                        _: {
                            ref: "$up.end"
                        }
                    }
                },
                inline: {
                    _: {
                        combine: ["end", "start"],
                        multi: true,
                        max: 2
                    },
                    end: {
                        _: {
                            ref: "$up.$up.block.end"
                        }
                    },
                    start: {
                        _: {
                            ref: "$up.$up.block.end"
                        }
                    }
                }
            },
            isolation: {
                _: {
                    values: {
                        auto: true,
                        isolate: true
                    }
                }
            },
            justify: {
                content: {
                    _: {
                        values: {
                            center: true,
                            start: true,
                            end: true,
                            "flex-start": true,
                            "flex-end": true,
                            left: true,
                            right: true,
                            normal: true,
                            "space-between": true,
                            "space-around": true,
                            "space-evenly": true,
                            stratch: true,
                            safe: true,
                            unsafe: true,
                            baseline: true,
                            first: true,
                            last: true
                        },
                        seperated: true
                    }
                },
                items: {
                    _: {
                        ref: "$up.content",
                        add: {
                            values: {
                                legacy: true
                            }
                        }
                    }
                },
                self: {
                    _: {
                        ref: "$up.content"
                    }
                }
            },
            left: {
                _: {
                    ref: "$up.bottom"
                }
            },
            letter: {
                spacing: {
                    _: {
                        values: {
                            normal: true
                        },
                        type: {
                            custom: true,
                            length: true
                        }
                    }
                }
            },
            lighting: {
                color: {
                    _: {
                        type: {
                            color: true
                        }
                    }
                }
            },
            line: {
                break: {
                    _: {
                        values: {
                            auto: true,
                            loose: true,
                            normal: true,
                            strict: true,
                            anywhere: true
                        }
                    }
                },
                height: {
                    _: {
                        values: {
                            normal: true
                        },
                        type: {
                            custom: true,
                            number: true,
                            length: true,
                            procent: true
                        }
                    },
                    step: {
                        _: {
                            type: {
                                length: true
                            }
                        }
                    }
                }
            },
            list: {
                style: {
                    _: {
                        combine: ["image", "position", "type"],
                        multi: true,
                        max: 3
                    },
                    image: {
                        _: {
                            type: {
                                custom: true,
                                image: true
                            },
                            values: {
                                none: true
                            }
                        }
                    },
                    position: {
                        _: {
                            values: {
                                inside: true,
                                outside: true
                            }
                        }
                    },
                    type: {
                        _: {
                            values: {
                                disc: true,
                                circle: true,
                                square: true,
                                decimal: true,
                                georgian: true,
                                "trad-chinese-informal": true,
                                kannada: true,
                                none: true
                            },
                            type: {
                                custom: true,
                                name: true
                            }
                        }
                    }
                }
            },
            margin: {
                _: {
                    type: {
                        custom: true,
                        length: true,
                        procent: true
                    },
                    values: {
                        auto: true
                    },
                    multi: true,
                    max: 4
                },
                block: {
                    _: {
                        combine: ["end", "start"],
                        multi: true,
                        max: 2
                    },
                    end: {
                        _: {
                            values: {
                                auto: true
                            },
                            type: {
                                custom: true,
                                length: true,
                                procent: true
                            }
                        }
                    },
                    start: {
                        _: {
                            ref: "$up.end"
                        }
                    }
                },
                bottom: {
                    _: {
                        ref: "$up.block.end"
                    }
                },
                inline: {
                    _: {
                        combine: ["end", "start"],
                        multi: true,
                        max: 2
                    },
                    end: {
                        _: {
                            ref: "$up.$up.block.end"
                        }
                    },
                    start: {
                        _: {
                            ref: "$up.$up.block.end"
                        }
                    }
                },
                left: {
                    _: {
                        ref: "$up.block.end"
                    }
                },
                right: {
                    _: {
                        ref: "$up.block.end"
                    }
                },
                top: {
                    _: {
                        ref: "$up.block.end"
                    }
                },
                trim: {
                    _: {
                        values: {
                            none: true,
                            "in-flow": true,
                            all: true
                        }
                    }
                }
            },
            marker: {
                end: {
                    _: {
                        values: {
                            none: true
                        },
                        type: {
                            custom: true,
                            image: true
                        }
                    }
                },
                mid: {
                    _: {
                        ref: "$up.end"
                    }
                },
                start: {
                    _: {
                        ref: "$up.end"
                    }
                }
            },
            mask: {
                _: {
                    type: {
                        image: true,
                        custom: true
                    },
                    values: {
                        add: true,
                        subtract: true,
                        intersect: true,
                        exclude: true,
                        "content-box": true,
                        "padding-box": true,
                        "border-box": true,
                        "margin-box": true,
                        "fill-box": true,
                        "stroke-box": true,
                        "view-box": true,
                        "no-clip": true,
                        border: true,
                        padding: true,
                        content: true,
                        text: true,
                        "repeat-x": true,
                        "repeat-y": true,
                        repeat: true,
                        space: true,
                        round: true,
                        "no-repeat": true,
                        cover: true,
                        contain: true,
                        auto: true,
                        none: true,
                        alpha: true,
                        luminance: true,
                        "match-source": true,
                        top: true,
                        right: true,
                        left: true,
                        bottom: true,
                        center: true,
                        length: true,
                        procent: true
                    },
                    multi: true,
                    seperated: true
                },
                border: {
                    _: {
                        combine: ["mode", "outset", "repeat", "slice", "source", "width"],
                        multi: true,
                        max: 3,
                        seperated: true,
                        seperator: {
                            '/': true
                        }
                    },
                    mode: {
                        _: {
                            values: {
                                luminance: true,
                                alpha: true
                            }
                        }
                    },
                    outset: {
                        _: {
                            values: {
                                length: true,
                                number: true
                            },
                            multi: true,
                            max: 4
                        }
                    },
                    repeat: {
                        _: {
                            values: {
                                stretch: true,
                                repeat: true,
                                round: true,
                                space: true
                            },
                            multi: true,
                            max: 2
                        }
                    },
                    slice: {
                        _: {
                            values: {
                                fill: true
                            },
                            type: {
                                custom: true,
                                number: true,
                                procent: true
                            }
                        }
                    },
                    source: {
                        _: {
                            type: {
                                custom: true,
                                image: true
                            },
                            values: {
                                none: true
                            }
                        }
                    },
                    width: {
                        _: {
                            type: {
                                length: true,
                                procent: true,
                                custom: true
                            },
                            values: {
                                auto: true
                            },
                            multi: true,
                            max: 4
                        }
                    }
                },
                clip: {
                    _: {
                        values: {
                            "content-box": true,
                            "padding-box": true,
                            "border-box": true,
                            "margin-box": true,
                            "fill-box": true,
                            "stroke-box": true,
                            "view-box": true,
                            "no-clip": true,
                            border: true,
                            padding: true,
                            content: true,
                            text: true
                        },
                        multi: true,
                        seperated: true
                    }
                },
                composite: {
                    _: {
                        values: {
                            add: true,
                            subtract: true,
                            intersect: true,
                            exclude: true
                        }
                    }
                },
                image: {
                    _: {
                        type: {
                            image: true,
                            custom: true
                        },
                        values: {
                            none: true
                        }
                    }
                },
                mode: {
                    _: {
                        values: {
                            alpha: true,
                            luminance: true,
                            "match-source": true
                        },
                        multi: true
                    }
                },
                origin: {
                    _: {
                        values: {
                            "content-box": true,
                            "padding-box": true,
                            "border-box": true,
                            "margin-box": true,
                            "fill-box": true,
                            "stroke-box": true,
                            "view-box": true,
                            border: true,
                            padding: true,
                            content: true
                        },
                        multi: true
                    }
                },
                position: {
                    _: {
                        combine: ['x', 'y'],
                        seperated: true,
                        multi: true,
                        max: 2
                    },
                    x: {
                        _: {
                            type: {
                                length: true,
                                procent: true,
                                custom: true
                            },
                            values: {
                                left: true,
                                center: true,
                                right: true
                            }
                        }
                    },
                    y: {
                        _: {
                            type: {
                                length: true,
                                procent: true,
                                custom: true
                            },
                            values: {
                                top: true,
                                center: true,
                                bottom: true
                            }
                        }
                    }
                },
                repeat: {
                    _: {
                        values: {
                            "repeat-x": true,
                            "repeat-y": true,
                            repeat: true,
                            space: true,
                            round: true,
                            "no-repeat": true
                        },
                        multi: true,
                        seperated: true
                    }
                },
                size: {
                    _: {
                        values: {
                            cover: true,
                            contain: true,
                            auto: true
                        },
                        type: {
                            custom: true,
                            length: true,
                            procent: true
                        },
                        multi: true,
                        seperated: true
                    }
                },
                type: {
                    _: {
                        values: {
                            luminance: true,
                            alpha: true
                        }
                    }
                }
            },
            max: {
                block: {
                    size: {
                        _: {
                            ref: "$up.$up.$up.max.height"
                        }
                    }
                },
                height: {
                    _: {
                        values: {
                            "max-content": true,
                            "min-content": true,
                            auto: true
                        },
                        type: {
                            custom: true,
                            functions: true,
                            length: true,
                            procent: true
                        },
                        functions: {
                            "fit-content": true
                        }
                    }
                },
                inline: {
                    size: {
                        _: {
                            ref: "$up.$up.$up.max.height"
                        }
                    }
                },
                width: {
                    _: {
                        ref: "$up.$up.max.height"
                    }
                }
            },
            min: {
                block: {
                    size: {
                        _: {
                            ref: "$up.$up.$up.max.height"
                        }
                    }
                },
                height: {
                    _: {
                        ref: "$up.$up.max.height"
                    }
                },
                inline: {
                    size: {
                        _: {
                            ref: "$up.$up.$up.max.height"
                        }
                    }
                },
                width: {
                    _: {
                        ref: "$up.$up.max.height"
                    }
                }
            },
            mix: {
                blend: {
                    mode: {
                        _: {
                            values: {
                                normal: true,
                                multiply: true,
                                screen: true,
                                overlay: true,
                                darken: true,
                                lighten: true,
                                "color-dodge": true,
                                "color-burn": true,
                                "hard-light": true,
                                "soft-light": true,
                                difference: true,
                                exclusion: true,
                                hue: true,
                                saturation: true,
                                color: true,
                                luminosity: true
                            }
                        }
                    }
                }
            },
            moz: {
                appearance: {
                    _: {
                        ref: "$up.$up.appearance"
                    }
                }
            },
            object: {
                fit: {
                    _: {
                        values: {
                            fill: true,
                            contain: true,
                            cover: true,
                            none: true,
                            "scale-down": true
                        }
                    }
                },
                position: {
                    _: {
                        type: {
                            procent: true,
                            length: true,
                            custom: true
                        },
                        values: {
                            right: true,
                            top: true,
                            left: true,
                            bottom: true,
                            center: true
                        },
                        multi: true,
                        max: 2
                    }
                }
            },
            offset: {
                _: {
                    combine: ["anchor", "distance", "path", "position", "rotate"],
                    multi: true,
                    max: 2,
                    seperated: true,
                    seperator: {
                        '/': true
                    }
                },
                position: {
                    _: {
                        type: {
                            custom: true,
                            procent: true,
                            length: true
                        },
                        values: {
                            auto: true,
                            top: true,
                            bottom: true,
                            left: true,
                            right: true,
                            center: true
                        },
                        multi: true,
                        max: 4
                    }
                },
                anchor: {
                    _: {
                        type: {
                            procent: true,
                            length: true,
                            custom: true
                        },
                        values: {
                            right: true,
                            top: true,
                            left: true,
                            bottom: true,
                            center: true
                        }
                    }
                },
                distance: {
                    _: {
                        type: {
                            length: true,
                            procent: true
                        }
                    }
                },
                path: {
                    _: {
                        values: {
                            none: true,
                            "margin-box": true,
                            "stroke-box": true
                        },
                        type: {
                            custom: true,
                            image: true
                        }
                    }
                },
                rotate: {
                    _: {
                        values: {
                            auto: true,
                            reverse: true
                        },
                        type: {
                            custom: true,
                            degree: true
                        },
                        seperated: true
                    }
                }
            },
            opacity: {
                _: {
                    type: {
                        number: true
                    }
                }
            },
            orphans: {
                _: {
                    type: {
                        integer: true
                    }
                }
            },
            order: {
                _: {
                    type: {
                        number: true
                    }
                }
            },
            outline: {
                _: {
                    combine: ["color", "style", "width"],
                    multi: true,
                    max: true
                },
                color: {
                    _: {
                        values: {
                            invert: true
                        },
                        type: {
                            custom: true,
                            color: true
                        }
                    }
                },
                offset: {
                    _: {
                        type: {
                            length: true
                        }
                    }
                },
                style: {
                    _: {
                        values: {
                            auto: true,
                            none: true,
                            dotted: true,
                            dashed: true,
                            solid: true,
                            groove: true,
                            double: true,
                            ridge: true,
                            inset: true,
                            outset: true
                        }
                    }
                },
                width: {
                    _: {
                        values: {
                            thin: true,
                            medium: true,
                            thick: true
                        },
                        type: {
                            custom: true,
                            length: true
                        }
                    }
                }
            },
            overflow: {
                _: {
                    values: {
                        visible: true,
                        hidden: true,
                        clip: true,
                        scroll: true,
                        auto: true
                    },
                    seperated: true
                },
                clip: {
                    margin: {
                        _: {
                            type: {
                                length: true
                            }
                        }
                    }
                },
                anchor: {
                    _: {
                        values: {
                            auto: true,
                            none: true
                        }
                    }
                },
                block: {
                    _: {
                        values: {
                            visible: true,
                            hidden: true,
                            scroll: true,
                            auto: true
                        }
                    }
                },
                inline: {
                    _: {
                        ref: "$up.block"
                    }
                },
                wrap: {
                    _: {
                        values: {
                            normal: true,
                            "break-word": true,
                            anywhere: true
                        }
                    }
                },
                x: {
                    _: {
                        ref: "$up.$up.overflow"
                    }
                },
                y: {
                    _: {
                        ref: "$up.$up.overflow"
                    }
                }
            },
            overscroll: {
                behavior: {
                    _: {
                        combine: ["x", "y"],
                        multi: true,
                        max: 2
                    },
                    block: {
                        _: {
                            values: {
                                auto: true,
                                contain: true,
                                none: true
                            }
                        }
                    },
                    inline: {
                        _: {
                            ref: "$up.block"
                        }
                    },
                    x: {
                        _: {
                            ref: "$up.block"
                        }
                    },
                    y: {
                        _: {
                            ref: "$up.block"
                        }
                    }
                }
            },
            padding: {
                _: {
                    ref: "$up.margin"
                },
                block: {
                    end: {
                        _: {
                            type: {
                                length: true,
                                procent: true
                            }
                        }
                    },
                    start: {
                        _: {
                            ref: "$up.end"
                        }
                    }
                },
                bottom: {
                    _: {
                        type: {
                            length: true,
                            procent: true
                        }
                    }
                },
                inline: {
                    end: {
                        _: {
                            ref: "$up.$up.$up.padding.block.end"
                        }
                    },
                    start: {
                        _: {
                            ref: "$up.$up.$up.padding.block.end"
                        }
                    }
                },
                left: {
                    _: {
                        ref: "$up.bottom"
                    }
                },
                right: {
                    _: {
                        ref: "$up.bottom"
                    }
                },
                top: {
                    _: {
                        ref: "$up.bottom"
                    }
                }
            },
            page: {
                break: {
                    inside: {
                        _: {
                            values: {
                                auto: true,
                                avoid: true
                            }
                        }
                    },
                    after: {
                        _: {
                            values: {
                                auto: true,
                                always: true,
                                avoid: true,
                                left: true,
                                right: true,
                                recto: true,
                                verso: true
                            }
                        }
                    },
                    before: {
                        _: {
                            ref: "$up.after"
                        }
                    }
                }
            },
            paint: {
                order: {
                    _: {
                        values: {
                            normal: true,
                            stroke: true,
                            fill: true,
                            markers: true
                        }
                    }
                }
            },
            perspective: {
                _: {
                    values: {
                        none: true
                    },
                    type: {
                        custom: true,
                        length: true
                    }
                },
                origin: {
                    _: {
                        values: {
                            left: true,
                            right: true,
                            center: true
                        },
                        type: {
                            custom: true,
                            length: true,
                            procent: true
                        },
                        seperated: true
                    }
                }
            },
            place: {
                content: {
                    _: {
                        combine: ["$up.$up.align.content", "$up.$up.justify.content"],
                        multi: true,
                        max: 2
                    }
                },
                items: {
                    _: {
                        combine: ["$up.$up.align.items", "$up.$up.justify.items"],
                        multi: true,
                        max: 2
                    }
                },
                self: {
                    _: {
                        combine: ["$up.$up.align.self", "$up.$up.justify.self"],
                        multi: true,
                        max: 2
                    }
                }
            },
            pointer: {
                events: {
                    _: {
                        values: {
                            auto: true,
                            none: true,
                            visiblePainted: true,
                            visibleFill: true,
                            visibleStroke: true,
                            visible: true,
                            painted: true,
                            fill: true,
                            stroke: true,
                            all: true
                        }
                    }
                }
            },
            position: {
                _: {
                    values: {
                        static: true,
                        fixed: true,
                        absolute: true,
                        relative: true,
                        sticky: true
                    }
                }
            },
            print: {
                color: {
                    adjust: {
                        _: {
                            values: {
                                economy: true,
                                exact: true
                            }
                        }
                    }
                }
            },
            quotes: {
                _: {
                    values: {
                        none: true,
                        auto: true
                    },
                    multi: true
                }
            },
            r: {
                _: {
                    type: {
                        number: true,
                        procent: true
                    }
                }
            },
            resize: {
                _: {
                    values: {
                        none: true,
                        both: true,
                        horizontal: true,
                        vertical: true,
                        block: true,
                        inline: true
                    }
                }
            },
            right: {
                _: {
                    ref: "$up.bottom"
                }
            },
            rotate: {
                _: {
                    values: {
                        none: true,
                        x: true,
                        y: true,
                        z: true
                    },
                    type: {
                        custom: true,
                        degree: true,
                        turn: true,
                        rad: true
                    },
                    multi: true
                }
            },
            row: {
                gap: {
                    _: {
                        type: {
                            length: true,
                            procent: true
                        }
                    }
                }
            },
            ruby: {
                align: {
                    _: {
                        values: {
                            start: true,
                            center: true,
                            "space-between": true,
                            "space-around": true
                        }
                    }
                },
                position: {
                    _: {
                        values: {
                            over: true,
                            under: true,
                            "inter-character": true,
                            alternate: true
                        }
                    }
                }
            },
            rx: {
                _: {
                    type: {
                        number: true
                    }
                }
            },
            ry: {
                _: {
                    type: {
                        number: true
                    }
                }
            },
            scale: {
                _: {
                    values: {
                        none: true
                    },
                    type: {
                        custom: true,
                        number: true
                    },
                    seperated: true
                }
            },
            scroll: {
                behavior: {
                    _: {
                        values: {
                            auto: true,
                            smooth: true
                        }
                    }
                },
                margin: {
                    block: {
                        end: {
                            _: {
                                type: {
                                    length: true
                                }
                            }
                        },
                        start: {
                            _: {
                                type: {
                                    length: true
                                }
                            }
                        }
                    },
                    bottom: {
                        _: {
                            type: {
                                length: true
                            }
                        }
                    },
                    inline: {
                        _: {
                            combine: ["start", "end"],
                            multi: true,
                            max: 2
                        },
                        end: {
                            _: {
                                type: {
                                    length: true
                                }
                            }
                        },
                        start: {
                            _: {
                                type: {
                                    length: true
                                }
                            }
                        }
                    },
                    left: {
                        _: {
                            type: {
                                length: true
                            }
                        }
                    },
                    right: {
                        _: {
                            type: {
                                length: true
                            }
                        }
                    },
                    top: {
                        _: {
                            type: {
                                length: true
                            }
                        }
                    }
                },
                padding: {
                    _: {
                        type: {
                            length: true,
                            number: true
                        },
                        multi: true,
                        max: 4
                    },
                    block: {
                        end: {
                            _: {
                                values: {
                                    auto: true
                                },
                                type: {
                                    custom: true,
                                    length: true,
                                    procent: true
                                }
                            }
                        },
                        start: {
                            _: {
                                ref: "$up.end"
                            }
                        }
                    },
                    bottom: {
                        _: {
                            ref: "$up.block.end"
                        }
                    },
                    inline: {
                        _: {
                            combine: ["end", "start"],
                            multi: true,
                            max: 2
                        },
                        end: {
                            _: {
                                values: {
                                    auto: true
                                },
                                type: {
                                    custom: true,
                                    length: true
                                }
                            }
                        },
                        start: {
                            _: {
                                ref: "$up.end"
                            }
                        }
                    },
                    left: {
                        _: {
                            ref: "$up.block.end"
                        }
                    },
                    right: {
                        _: {
                            ref: "$up.block.end"
                        }
                    },
                    top: {
                        _: {
                            ref: "$up.block.end"
                        }
                    }
                },
                snap: {
                    coordinate: {
                        _: {
                            type: {
                                length: true,
                                custom: true,
                                procent: true
                            },
                            values: {
                                left: true,
                                right: true,
                                center: true,
                                bottom: true,
                                top: true
                            },
                            multi: true,
                            max: 2,
                            seperated: true
                        }
                    },
                    destination: {
                        _: {
                            ref: "$up.coordinate"
                        }
                    },
                    points: {
                        x: {
                            _: {
                                type: {
                                    custom: true,
                                    functions: true
                                },
                                functions: {
                                    repeat: true
                                },
                                values: {
                                    none: true
                                }
                            }
                        },
                        y: {
                            _: {
                                ref: '$up.x'
                            }
                        }
                    },
                    align: {
                        _: {
                            values: {
                                none: true,
                                start: true,
                                end: true,
                                center: true
                            },
                            seperated: true
                        }
                    },
                    type: {
                        _: {
                            values: {
                                none: true,
                                x: true,
                                y: true,
                                block: true,
                                inline: true,
                                both: true,
                                mandatory: true,
                                proximity: true
                            },
                            seperated: true
                        }
                    }
                }
            },
            scrollbar: {
                color: {
                    _: {
                        values: {
                            auto: true,
                            dark: true,
                            light: true
                        },
                        type: {
                            custom: true,
                            color: true
                        },
                        seperated: true
                    }
                },
                gutter: {
                    _: {
                        values: {
                            auto: true,
                            stable: true,
                            "both-edges": true
                        },
                        max: 2,
                        multi: true
                    }
                },
                width: {
                    _: {
                        values: {
                            auto: true,
                            thin: true,
                            none: true
                        }
                    }
                }
            },
            shape: {
                image: {
                    threshold: {
                        _: {
                            type: {
                                number: true
                            }
                        }
                    }
                },
                margin: {
                    _: {
                        type: {
                            length: true,
                            procent: true
                        }
                    }
                },
                outside: {
                    _: {
                        values: {
                            none: true,
                            "margin-box": true,
                            "content-box": true,
                            "border-box": true,
                            "padding-box": true
                        },
                        type: {
                            custom: true,
                            image: true
                        }
                    }
                },
                rendering: {
                    _: {
                        values: {
                            auto: true,
                            optimizeSpeed: true,
                            crispEdges: true,
                            geometricPrecision: true
                        }
                    }
                }
            },
            stop: {
                color: {
                    _: {
                        type: {
                            color: true
                        }
                    }
                },
                opacity: {
                    _: {
                        type: {
                            number: true
                        }
                    }
                }
            },
            stroke: {
                _: {
                    values: {
                        none: true,
                        "context-fill": true,
                        "context-stroke": true
                    },
                    type: {
                        custom: true,
                        color: true,
                        functions: true
                    },
                    functions: {
                        url: true
                    }
                },
                dasharray: {
                    _: {
                        type: {
                            number: true
                        },
                        seperated: true
                    }
                },
                dashoffset: {
                    _: {
                        type: {
                            number: true
                        }
                    }
                },
                linecap: {
                    _: {
                        values: {
                            butt: true,
                            round: true,
                            square: true
                        }
                    }
                },
                linejoin: {
                    _: {
                        values: {
                            arcs: true,
                            bevel: true,
                            miter: true,
                            "miter-clip": true,
                            round: true
                        }
                    }
                },
                miterlimit: {
                    _: {
                        type: {
                            number: true
                        }
                    }
                },
                opacity: {
                    _: {
                        type: {
                            number: true,
                            procent: true
                        }
                    }
                },
                width: {
                    _: {
                        type: {
                            number: true,
                            length: true,
                            procent: true
                        }
                    }
                }
            },
            tab: {
                size: {
                    _: {
                        type: {
                            length: true,
                            number: true
                        }
                    }
                }
            },
            table: {
                layout: {
                    _: {
                        values: {
                            auto: true,
                            fixed: true
                        }
                    }
                }
            },
            text: {
                size: {
                    adjust: {
                        _: {
                            type: {
                                procent: true,
                                custom: true
                            },
                            values: {
                                none: true,
                                auto: true
                            }
                        }
                    }
                },
                align: {
                    _: {
                        values: {
                            left: true,
                            right: true,
                            center: true,
                            justify: true,
                            "justify-all": true,
                            start: true,
                            end: true,
                            "match-parent": true
                        }
                    },
                    last: {
                        _: {
                            values: {
                                auto: true,
                                start: true,
                                end: true,
                                left: true,
                                right: true,
                                center: true,
                                justify: true
                            }
                        }
                    }
                },
                anchor: {
                    _: {
                        values: {
                            start: true,
                            middle: true,
                            end: true
                        }
                    }
                },
                combine: {
                    upright: {
                        _: {
                            values: {
                                none: true,
                                all: true,
                                digits: true
                            },
                            type: {
                                custom: true,
                                number: true
                            },
                            seperated: true
                        }
                    }
                },
                decoration: {
                    _: {
                        join: {
                            "$up.line": true,
                            "$up.color": true,
                            "$up.style": true,
                            "$up.thickness": true
                        }
                    },
                    color: {
                        _: {
                            type: {
                                color: true
                            }
                        }
                    },
                    line: {
                        _: {
                            values: {
                                none: true,
                                underline: true,
                                overline: true,
                                "line-through": true,
                                blink: true
                            },
                            seperated: true
                        }
                    },
                    skip: {
                        _: {
                            type: {
                                custom: true
                            },
                            values: {
                                none: true,
                                objects: true,
                                spaces: true,
                                'leading-spaces': true,
                                'trailing-spaces': true,
                                edges: true,
                                'box-decoration': true
                            },
                            multi: true,
                            max: 2
                        },
                        ink: {
                            _: {
                                values: {
                                    auto: true,
                                    all: true,
                                    none: true
                                }
                            }
                        }
                    },
                    style: {
                        _: {
                            values: {
                                solid: true,
                                double: true,
                                dotted: true,
                                dashed: true,
                                wavy: true
                            }
                        }
                    },
                    thickness: {
                        _: {
                            values: {
                                auto: true,
                                "from-font": true
                            },
                            type: {
                                custom: true,
                                length: true,
                                procent: true
                            }
                        }
                    }
                },
                emphasis: {
                    _: {
                        combine: ["style", "color"],
                        multi: true,
                        max: 2
                    },
                    color: {
                        _: {
                            type: {
                                color: true
                            }
                        }
                    },
                    position: {
                        _: {
                            values: {
                                over: true,
                                under: true,
                                right: true,
                                left: true
                            },
                            seperated: true
                        }
                    },
                    style: {
                        _: {
                            values: {
                                none: true,
                                filled: true,
                                open: true,
                                dot: true,
                                circle: true,
                                "double-circle": true,
                                triangle: true,
                                sesame: true
                            },
                            type: {
                                custom: true
                            }
                        }
                    }
                },
                indent: {
                    _: {
                        values: {
                            "each-line": true,
                            hanging: true
                        },
                        type: {
                            custom: true,
                            length: true,
                            procent: true
                        }
                    }
                },
                justify: {
                    _: {
                        values: {
                            none: true,
                            auto: true,
                            "inter-word": true,
                            "inter-character": true,
                            distribute: true
                        }
                    }
                },
                orientation: {
                    _: {
                        values: {
                            mixed: true,
                            upright: true,
                            "sideways-right": true,
                            sideways: true,
                            "use-glyph-orientation": true
                        }
                    }
                },
                overflow: {
                    _: {
                        values: {
                            clip: true,
                            ellipsis: true,
                            fade: true
                        },
                        type: {
                            custom: true
                        }
                    }
                },
                rendering: {
                    _: {
                        values: {
                            auto: true,
                            optimizeSpeed: true,
                            optimizeLegibility: true,
                            geometricPrecision: true
                        }
                    }
                },
                shadow: {
                    _: {
                        type: {
                            length: true,
                            color: true
                        },
                        seperated: true,
                        multi: true
                    }
                },
                transform: {
                    _: {
                        values: {
                            none: true,
                            capitalize: true,
                            uppercase: true,
                            lowercase: true,
                            "full-width": true,
                            "full-size-kana": true
                        }
                    }
                },
                underline: {
                    offset: {
                        _: {
                            values: {
                                auto: true
                            },
                            type: {
                                custom: true,
                                length: true,
                                procent: true
                            }
                        }
                    },
                    position: {
                        _: {
                            values: {
                                auto: true,
                                under: true,
                                left: true,
                                right: true
                            },
                            seperated: true
                        }
                    }
                }
            },
            top: {
                _: {
                    ref: "$up.$up.bottom"
                }
            },
            touch: {
                action: {
                    _: {
                        values: {
                            auto: true,
                            none: true,
                            "pan-x": true,
                            "pan-left": true,
                            "pan-right": true,
                            "pan-y": true,
                            "pan-up": true,
                            "pan-down": true,
                            "pinch-zoom": true,
                            manipulation: true
                        }
                    }
                }
            },
            transform: {
                _: {
                    values: {
                        none: true
                    },
                    type: {
                        custom: true,
                        functions: true
                    },
                    functions: {
                        matrix: true,
                        matrix3d: true,
                        perspective: true,
                        rotate: true,
                        rotate3d: true,
                        rotateX: true,
                        rotateY: true,
                        rotateZ: true,
                        translate: true,
                        translate3d: true,
                        translateX: true,
                        translateY: true,
                        translateZ: true,
                        scale: true,
                        scale3d: true,
                        scaleX: true,
                        scaleY: true,
                        scaleZ: true,
                        skew: true,
                        skewX: true,
                        skewY: true
                    }
                },
                box: {
                    _: {
                        values: {
                            "content-box": true,
                            "border-box": true,
                            "fill-box": true,
                            "stroke-box": true,
                            "view-box": true
                        }
                    }
                },
                origin: {
                    _: {
                        type: {
                            length: true,
                            procent: true,
                            custom: true
                        },
                        values: {
                            center: true,
                            left: true,
                            right: true,
                            top: true,
                            bottom: true
                        },
                        multi: true,
                        max: 3
                    }
                },
                style: {
                    _: {
                        values: {
                            flat: true,
                            "preserve-3d": true
                        }
                    }
                }
            },
            transition: {
                _: {
                    combine: ["delay", "duration", "property", "timing.function"],
                    multi: true,
                    max: 4,
                    seperated: true
                },
                delay: {
                    _: {
                        type: {
                            time: true
                        },
                        multi: true
                    }
                },
                duration: {
                    _: {
                        ref: "$up.delay"
                    }
                },
                property: {
                    _: {
                        values: {
                            none: true,
                            all: true
                        },
                        type: {
                            custom: true,
                            name: true
                        }
                    }
                },
                timing: {
                    function: {
                        _: {
                            values: {
                                ease: true,
                                "ease-in": true,
                                "ease-out": true,
                                "ease-in-out": true,
                                linear: true,
                                "step-start": true,
                                "step-end": true
                            },
                            type: {
                                custom: true,
                                functions: true
                            },
                            functions: {
                                "cubic-bezier": true,
                                steps: true
                            }
                        }
                    }
                }
            },
            translate: {
                _: {
                    type: {
                        length: true,
                        procent: true
                    },
                    seperated: true
                }
            },
            unicode: {
                bidi: {
                    _: {
                        values: {
                            normal: true,
                            embed: true,
                            isolate: true,
                            "bidi-override": true,
                            "isolate-override": true,
                            plaintext: true
                        }
                    }
                }
            },
            user: {
                select: {
                    _: {
                        values: {
                            none: true,
                            auto: true,
                            text: true,
                            contain: true,
                            all: true
                        }
                    }
                }
            },
            vector: {
                effect: {
                    _: {
                        values: {
                            none: true,
                            "non-scaling-stroke": true,
                            "non-scaling-size": true,
                            "non-rotation": true,
                            "fixed-position": true
                        }
                    }
                }
            },
            vertical: {
                align: {
                    _: {
                        values: {
                            baseline: true,
                            sub: true,
                            super: true,
                            "text-top": true,
                            "text-bottom": true,
                            middle: true,
                            top: true,
                            bottom: true
                        },
                        type: {
                            custom: true,
                            length: true,
                            procent: true
                        }
                    }
                }
            },
            visibility: {
                _: {
                    values: {
                        visible: true,
                        hidden: true,
                        collapse: true
                    }
                }
            },
            white: {
                space: {
                    _: {
                        values: {
                            normal: true,
                            nowrap: true,
                            pre: true,
                            "pre-wrap": true,
                            "pre-line": true,
                            "break-spaces": true
                        }
                    }
                }
            },
            widows: {
                _: {
                    type: {
                        integer: true
                    }
                }
            },
            webkit: {
                appearance: {
                    _: {
                        ref: "$up.$up.appearance"
                    }
                }
            },
            width: {
                _: {
                    ref: "$up.height"
                }
            },
            will: {
                change: {
                    _: {
                        values: {
                            auto: true,
                            "scroll-position": true,
                            contents: true
                        },
                        type: {
                            custom: true,
                            name: true
                        },
                        seperated: true
                    }
                }
            },
            word: {
                break: {
                    _: {
                        values: {
                            normal: true,
                            "break-all": true,
                            "kepp-all": true
                        }
                    }
                },
                spacing: {
                    _: {
                        values: {
                            normal: true
                        },
                        type: {
                            custom: true,
                            length: true,
                            procent: true
                        }
                    }
                }
            },
            writing: {
                mode: {
                    _: {
                        values: {
                            "horizontal-tb": true,
                            "vertical-rl": true,
                            "vertical-lr": true
                        }
                    }
                }
            },
            x: {
                _: {
                    type: {
                        number: true
                    }
                }
            },
            y: {
                _: {
                    type: {
                        number: true
                    }
                }
            },
            z: {
                index: {
                    _: {
                        type: {
                            number: true
                        }
                    }
                }
            }
        }
    }
}
Ī.x = Ī => {
        const {
            dictionary: css
        } = Ī.y(Ī);
        class TabJF_Syntax {
            groups = [];
            ends = [];
            groupPath = [];
            init() {
                const start = this.render.hidden;
                let end = start + this.render.linesLimit;
                if (end > this.render.content.length) {
                    end = this.render.content.length
                }
                const lines = this.render.content.slice(0, end);
                this.syntax.groups = [this.settings.syntax];
                this.syntax.ends = [null];
                this.syntax.highlightLines(lines, start)
            }
            fireTrigger(subset, scope, path, args) {
                if (!subset) return;
                const triggers = this.syntax.findTriggers(subset, path);
                if (!triggers) return;
                triggers.forEach(func => {
                    func.bind(scope)(...args)
                })
            }
            findTriggers(subset, path) {
                if (path.length == 1) return subset[path[0]];
                if (!subset[path[path.length - 1]]) return false;
                return this.syntax.findTriggers(subset[path[path.length - 1]], path.slice(1))
            }
            highlightLines(lines, start) {
                for (let i = 0; i < lines.length; i++) {
                    this.render.content[i + start].ends = this.get.clone(this.syntax.ends);
                    this.render.content[i + start].groupPath = this.get.clone(this.syntax.groupPath);
                    const line = lines[i];
                    const sentence = this.get.sentence(line);
                    let group = this.syntax.groups[0];
                    this.syntax.fireTrigger(group?.triggers, this.settings.syntax, ['line', 'start'], [i + start, line, sentence, group.subset.sets, this.syntax]);
                    const res = this.syntax.validateResults(this.syntax.paint(sentence));
                    if (res.words.length == 0) {
                        res.words.push(this.syntax.create.span({}, ''))
                    }
                    this.render.content[i + start].content = res.words;
                    group = this.syntax.groups[0];
                    this.syntax.fireTrigger(group?.triggers, this.settings.syntax, ['line', 'end'], [i + start, line, group.subset.sets, this.syntax])
                }
            }
            update() {
                let start = this.activated ? this.pos.line : this.render.hidden;
                start = this.syntax.getGroupPathLineNumber(start);
                const aStart = this.render.hidden;
                const end = this.render.linesLimit;
                const lines = this.render.content.slice(start, aStart + end);
                this.syntax.groups = this.syntax.createGroups(this.get.clone(this.render.content[start].groupPath), this.settings.syntax);
                this.syntax.groups.push(this.settings.syntax);
                this.syntax.ends = this.render.content[start].ends;
                this.syntax.groupPath = this.render.content[start].groupPath;
                this.syntax.highlightLines(lines, start)
            }
            getGroupPathLineNumber(start) {
                for (let i = start; i >= 0; i--) {
                    if (this.render.content[i].groupPath) {
                        return i
                    }
                }
                throw new Error('Group Path was not found on any line')
            }
            createGroups(directions, schemats, groups = []) {
                if (directions.length == 0) {
                    return groups
                }
                const schemat = schemats.subset.sets[directions[0]];
                groups.unshift(schemat);
                directions.shift();
                return this.syntax.createGroups(directions, schemat, groups)
            }
            validateResults(res) {
                if (res.sentence.length > 0) {
                    return {
                        words: res.words.concat(this.syntax.validateResults(this.syntax.paint(res.sentence), true).words),
                        sentence: ''
                    }
                }
                return res
            }
            findEndLandmark(sentence, end) {
                let endFound = false;
                if (typeof end == 'object') {
                    Object.keys(end).forEach(function(endLandmark) {
                        if (sentence.substr(-endLandmark.length) == endLandmark) {
                            endFound = endLandmark;
                            return
                        }
                    })
                } else if (sentence.substr(-end.length) == end) {
                    endFound = end
                }
                return endFound
            }
            paint(sentence, words = []) {
                let group = this.syntax.groups[0];
                let subset = group.subset;
                subset.sets = Object.assign({}, this.settings.syntax.global ?? {}, subset.sets);
                let end = this.syntax.ends[0];
                for (var i = 0; i < sentence.length; i++) {
                    let letter = sentence[i];
                    let endFound = false;
                    if (end !== null) {
                        endFound = this.syntax.findEndLandmark(sentence.substr(0, i + 1), end)
                    }
                    if (endFound) {
                        const results = this.syntax.endSubsetChecks(i, letter, endFound, words, sentence, subset, group);
                        words = results.words;
                        sentence = results.sentence;
                        i = results.i;
                        group = this.syntax.groups[0];
                        subset = group.subset;
                        end = this.syntax.ends[0];
                        continue
                    }
                    if (subset?.sets && ((subset?.sets[letter] && !subset?.sets[letter]?.whole) || (subset?.sets[sentence.substr(0, i + 1)] && !subset?.sets[sentence.substr(0, i + 1)]?.whole))) {
                        const realLetter = subset?.sets[sentence.substr(0, i + 1)] ? sentence.substr(0, i + 1) : letter;
                        const results = this.syntax.splitWord(subset, i, realLetter, words, sentence);
                        words = results.words;
                        sentence = results.sentence;
                        i = results.i;
                        group = this.syntax.groups[0];
                        subset = group.subset;
                        end = this.syntax.ends[0]
                    } else if (group?.selfref && letter == group?.start) {
                        let oldOne = {
                            subset: {
                                sets: {
                                    [group.start]: group
                                }
                            }
                        };
                        const resultsFirstWord = this.syntax.splitWord(subset, i, letter, words, sentence.slice(0, i));
                        words = resultsFirstWord.words;
                        const results = this.syntax.splitWord(oldOne.subset, 0, letter, words, sentence.slice(i));
                        words = results.words;
                        sentence = results.sentence;
                        i = results.i + 1;
                        continue
                    }
                }
                if (sentence.length > 0) {
                    let attr = this.syntax.getAttrsFromSet(this.syntax.getSet(subset, sentence), sentence, words, '', sentence, subset);
                    words.push(this.syntax.create.span(attr, sentence));
                    sentence = ''
                }
                return {
                    words,
                    sentence
                }
            }
            endSubsetChecks(i, letter, end, words, sentence, subset, group) {
                let word = sentence.substring(0, (i + 1) - end.length);
                if (word.length != 0) {
                    let wordSet = this.syntax.getSet(subset, word);
                    words.push(this.syntax.create.span(this.syntax.getAttrsFromSet(wordSet, word, words, letter, sentence, subset), word))
                }
                sentence = sentence.substring((i + 1) - end.length);
                this.syntax.endSubset();
                this.syntax.fireTrigger(group?.triggers, group, ['end'], [i, word, words, letter, sentence, group, this.syntax]);
                let index = -1;
                if (sentence[0] == group.start || (sentence[0] == this.syntax.groups[0].end || (typeof this.syntax.groups[0].end == && this.syntax.groups[0].end[sentence[0]]))) {
                    index = 0
                }
                return {
                    words,
                    sentence,
                    i: index
                }
            }
            endSubset() {
                this.syntax.groups.shift();
                this.syntax.ends.shift();
                this.syntax.groupPath.pop()
            }
            splitWord(subset, i, letter, words, sentence) {
                    const letterSet = subset?.sets[letter];
                    let word = sentence.substr(0, i - (letter.length - 1));
                    if (word.length != 0) {
                        let wordSet = this.syntax.getSet(subset, word);
                        let attrs = wordSet.attrs;
                        if (wordSet?.ignore) {
                            attrs: {
                                style: 'color:#FFF;'
                            }
                        }
                        if (wordSet?.run) {
                            const results = wordSet.run.bind(wordSet);
                            attrs = results(word, words, letter, sentence, subset.sets, subset, this.syntax)
                        }
                        words.push(this.syntax.create.span(attrs, word))
                    }
                    if (letterSet?.single) {
                        let attrs = letterSet.attrs ?? {
                            style:;
                            if (letterSet?.run) {
                                const results = letterSet.run.bind(letterSet);
                                attrs = results(word, words, letter, sentence, subset.sets, subset, this.syntax)
                            }
                            words.push(this.syntax.create.span(attrs, sentence.substr(i, letter.length)));i += letter.length;word += letter
                        }
                        sentence = sentence.substring(word.length);
                        i = word.length > 0 ? -1 : i;
                        if (subset.sets[letter]?.subset) {
                            words.push(this.syntax.create.span(letterSet.attrs, sentence.substr(0, letter.length)));
                            const res = this.syntax.startNewSubset(letter, letterSet, word, words, sentence.substring(letter.length), subset);
                            words = res.words;
                            sentence = res.sentence;
                            i = res.i
                        }
                        return {
                            words,
                            sentence
                        }
                    }
                    startNewSubset(letter, letterSet, word, words, sentence, subset) {
                        this.syntax.groupPath.push(letter);
                        const group = subset.sets[letter];
                        console.log(letter, letterSet, word, words, sentence, subset);
                        this.syntax.fireTrigger(group?.triggers, group, ['start'], [letter, letterSet, word, words, sentence, subset, this.syntax]);
                        if (!letterSet?.single && !subset.sets[letter]?.subset) {
                            words.push(this.syntax.create.span(subset.sets[letter].attrs, letter))
                        }
                        if (!subset.sets[letter]?.start) {
                            subset.sets[letter].start = letter
                        }
                        this.syntax.groups.unshift(subset.sets[letter]);
                        this.syntax.ends.unshift(subset.sets[letter].end);
                        const res = this.syntax.paint(sentence, words);
                        return {
                            words: res.words,
                            sentence: res.sentence,
                            i: res.i
                        }
                    }
                    getSet(group, word) {
                        let set = group.sets[word[0]] || group.sets[word] || group.sets['default']. || {
                            attrs: {
                                class: .
                                'mistake';
                                if (set.whole && group.sets[word[0]]) {
                                    return group.sets[word] || group.sets['default']. || {
                                        attrs: {
                                            class: .
                                            'mistake'
                                        }
                                    }
                                }
                                returnset
                            }
                            getAttrsFromSet(set, word, words, letter, sentence, group) {
                                let attrs = set.attrs;
                                if (set?.ignore) {
                                    attrs: {
                                        style: 'color:#FFF;'
                                    }
                                }
                                if (set?.run) {
                                    const results = set.run.bind(set);
                                    attrs = results(word, words, letter, sentence, group.sets, group, this.syntax)
                                }
                                return attrs
                            }
                        }
                        return {}
                    }
                    Ī.a = Ī => {
                        class TabJF_Action {
                            copyGround = null;
                            createCopyGround() {
                                this.action.copyGround = document.createElement('div');
                                this.render.overflow.insertBefore(this.action.copyGround, this.editor)
                            }
                            copy() {
                                const clipboard = this.get.selectedLines();
                                const event = this.event.dispatch('tabJFCopy', {
                                    pos: this.get.clonedPos(),
                                    event: null,
                                    clipboard: this.get.clone(clipboard)
                                });
                                if (event.defaultPrevented) return;
                                this.clipboard = this.get.clone(clipboard);
                                const ground = this.action.copyGround;
                                this.truck.import(this.clipboard, false, 0, false, false, ground, false);
                                let firstText = ground.children[0].children[0].childNodes[0];
                                let lastText = ground.children[ground.children.length - 1];
                                lastText = lastText.children[lastText.children.length - 1];
                                lastText = lastText.childNodes[lastText.childNodes.length - 1];
                                const range = new Range;
                                range.setStart(firstText, 0);
                                range.setEnd(lastText, lastText.nodeValue.length);
                                this.get.selection().removeAllRanges();
                                this.get.selection().addRange(range);
                                setTimeout(function() {
                                    document.execCommand('copy');
                                    this.copiedHere = true;
                                    ground.innerHTML = '';
                                    this.checkSelect()
                                }.bind(this), 0)
                            }
                            paste() {
                                const event = this.event.dispatch('tabJFPaste', {
                                    pos: this.get.clonedPos(),
                                    event: null,
                                    clipboard: this.get.clone(this.clipboard)
                                });
                                if (event.defaultPrevented) return;
                                this.remove.selected();
                                const clipboard = this.get.clone(this.clipboard);
                                const first = clipboard[0];
                                const last = clipboard[clipboard.length - 1];
                                let firstLine = this.render.content[this.pos.line];
                                let firstLineSpan = firstLine.content[this.pos.childIndex];
                                let firstPreText = this.replace.spaceChars(firstLineSpan.content).substr(0, this.pos.letter);
                                let firstSufText = this.replace.spaceChars(firstLineSpan.content).substr(this.pos.letter);
                                firstLineSpan.content = firstPreText;
                                let firstLineSpans = firstLine.content.splice(this.pos.childIndex + 1);
                                firstLine.content = firstLine.content.concat(first.content);
                                let middleLines = this.get.clone(clipboard.slice(1, clipboard.length - 1));
                                let lastLetter, lastChildIndex;
                                if (clipboard.length > 1) {
                                    let lastLine = clipboard[clipboard.length - 1];
                                    lastChildIndex = lastLine.content.length - 1;
                                    lastLetter = this.replace.spaceChars(lastLine.content[lastLine.content.length - 1].content).length;
                                    lastLine.content[lastLine.content.length - 1].content;
                                    content += firstSufText;
                                    lastLine.content = lastLine.content.concat(firstLineSpans);
                                    middleLines = middleLines.concat([lastLine])
                                } else {
                                    lastLetter = first.content[first.content.length - 1].content.length;
                                    lastChildIndex = this.pos.childIndex + first.content.length;
                                    firstLine.content[firstLine.content.length - 1].content;
                                    content += firstSufText;
                                    firstLine.content = firstLine.content.concat(firstLineSpans)
                                }
                                this.render.content.splice(this.pos.line + 1, 0, ...middleLines);
                                this.render.move.page();
                                this.render.set.overflow(null, ((this.pos.line + clipboard.length - 1) - (Math.floor(this.render.linesLimit / 2))) * this.settings.line);
                                this.caret.refocus(lastLetter, this.pos.line + clipboard.length - 1, lastChildIndex);
                                this.lastX = this.get.realPos().x;
                                this.render.update.minHeight();
                                this.render.update.scrollWidth();
                                this.update.selection.start();
                                this.update.page()
                            }
                            cut() {
                                const event = this.event.dispatch('tabJFCut', {
                                    pos: this.get.clonedPos(),
                                    event: null,
                                    clipboard: this.get.clone(this.clipboard)
                                });
                                if (event.defaultPrevented) return;
                                this.action.copy();
                                this.remove.selected();
                                this.render.update.minHeight();
                                this.render.update.scrollWidth()
                            }
                            undo() {
                                const versionBefore = this.get.clone(this._save.versions[this._save.version] ?? {});
                                const versionNumberBefore = this._save.version;
                                const event = this.event.dispatch('tabJFUndo', {
                                    pos: this.get.clonedPos(),
                                    event: null,
                                    versionNumber: this._save.version - 1,
                                    version: this.get.clone(this._save.versions[this._save.version - 1] ?? {}),
                                    versionNumberBefore,
                                    versionBefore
                                });
                                if (event.defaultPrevented) return;
                                this._save.restore();
                                this.lastX = this.get.realPos().x;
                                this.render.update.minHeight();
                                this.render.update.scrollWidth()
                            }
                            redo() {
                                const versionBefore = this.get.clone(this._save.versions[this._save.version] ?? {});
                                const versionNumberBefore = this._save.version;
                                const event = this.event.dispatch('tabJFRedo', {
                                    pos: this.get.clonedPos(),
                                    event: null,
                                    versionNumber: this._save.version + 1,
                                    version: this.get.clone(this._save.versions[this._save.version + 1] ?? {}),
                                    versionNumberBefore
                                });
                                if (event.defaultPrevented) return;
                                this._save.recall();
                                this.lastX = this.get.realPos().x;
                                this.render.update.minHeight();
                                this.render.update.scrollWidth()
                            }
                            selectAll() {
                                const event = this.event.dispatch('tabJFSelectAll', {
                                    pos: this.get.clonedPos(),
                                    event: null
                                });
                                if (event.defaultPrevented) return;
                                this.update.selection.start(0, 0, 0);
                                const last = this.render.content[this.render.content.length - 1];
                                const lastSpan = last.content[last.content.length - 1];
                                const lastNode = this.replace.spaceChars(lastSpan.content);
                                this.update.selection.end(lastNode.length, this.render.content.length - 1, last.content.length - 1);
                                this.selection.active = true;
                                this.checkSelect()
                            }
                        }
                        return {}
                    }
                    Ī.b = Ī => {
                        class TabJF_Caret {
                            el = null;
                            isActive = false;
                            isVisible() {
                                return this.caret.isActive && (this.pos.line >= this.render.hidden && this.pos.line <= this.render.hidden + this.render.linesLimit)
                            }
                            scrollToX() {
                                const left = this.render.overflow.scrollLeft;
                                const caretPos = this.caret.getPos();
                                if (this.render.overflow.offsetWidth + left - 10 - this.settings.left < caretPos.left) {
                                    this.render.move.overflow(caretPos.left - (this.render.overflow.offsetWidth + left - 10 - this.settings.left), 0)
                                } else if (caretPos.left < left + 10 + this.settings.left) {
                                    this.render.move.overflow(-(left + 10 + this.settings.left - caretPos.left), 0)
                                }
                            }
                            scrollToY() {
                                const top = this.render.overflow.scrollTop;
                                const caretPos = this.caret.getPos();
                                if (this.render.overflow.offsetHeight + top - 10 < caretPos.top) {
                                    this.render.move.overflow(0, caretPos.top - (this.render.overflow.offsetHeight + top - 10), )
                                } else if (caretPos.top < top + 10) {
                                    this.render.move.overflow(-(top + 10 - caretPos.top), 0)
                                }
                            }
                            set(x, y) {
                                this.caret.el.style.left = x + 'px';
                                this.caret.el.style.top = y + 'px'
                            }
                            setByChar(letter, line, el = null) {
                                if (el) this.pos.el = el;
                                let posX = this.font.calculateWidth(this.pos.el.innerText.slice(0, letter), this.pos.el);
                                this.pos.letter = letter;
                                this.pos.line = line;
                                this.caret.set(posX + this.settings.left + this.pos.el.offsetLeft, (line * this.settings.line))
                            }
                            getPos() {
                                return {
                                    top: this.caret.el.style.top.replace('px', ''),
                                    left: this.caret.el.style.left.replace('px', '')
                                }
                            }
                            create(parent) {
                                const caret = document.createElement("div");
                                caret.className = 'caret';
                                parent.insertBefore(caret, parent.childNodes[0]);
                                return caret
                            }
                            hide() {
                                if (this.caret.el) this.caret.el.style.display = "none";
                                this.caret.isActive = false
                            }
                            show() {
                                if (this.caret.el) this.caret.el.style.display = "block";
                                this.caret.isActive = true
                            }
                            refocus(letter = this.pos.letter, line = this.pos.line, childIndex = this.pos.childIndex) {
                                this.pos.letter = letter;
                                this.pos.line = line;
                                this.pos.childIndex = childIndex;
                                if (!this.caret.isVisible()) return false;
                                line = this.get.lineByPos(this.pos.line);
                                if (this.pos.line <= this.render.hidden + this.render.linesLimit && this.pos.line >= this.render.hidden && line) {
                                    this.pos.el = line.childNodes[childIndex];
                                    this.caret.setByChar(this.pos.letter, this.pos.line, line.childNodes[this.pos.childIndex]);
                                    return true
                                }
                                return false
                            }
                            recalculatePos(first = true) {
                                const line = this.get.lineByPos(this.activated ? this.pos.line : this.render.hidden);
                                if (!line) return;
                                if (first) {
                                    this.pos.letter = this.lastX;
                                    this.pos.childIndex = 0
                                }
                                if (!line.children[this.pos.childIndex] && first) {
                                    this.pos.childIndex--
                                }
                                this.pos.el = line.children[this.pos.childIndex];
                                const text = this.pos.el.innerText;
                                if (text.length < this.pos.letter) {
                                    if (this.pos.childIndex == line.children.length - 1) {
                                        this.pos.letter = text.length;
                                        return
                                    }
                                    this.pos.letter;
                                    letter -= text.length;
                                    if (this.pos.childIndex < line.children.length - 1) {
                                        this.pos.childIndex++
                                    }
                                    this.caret.recalculatePos(false);
                                    return
                                }
                            }
                        }
                        return {}
                    }
                    Ī.c = Ī => {
                        class TabJF_Clear {
                            editor() {
                                this.editor.innerHTML = '';
                                if (this.caret.el) this.editor.appendChild(this.caret.el)
                            }
                        }
                        return {}
                    }
                    Ī.d = Ī => {
                        class TabJF_End {
                            select() {
                                this.get.selection().empty();
                                const sel = this.selection;
                                sel.update = false;
                                sel.reverse = false;
                                sel.active = false;
                                sel.expanded = false;
                                sel.end = {
                                    line: -1,
                                    letter: -1,
                                    node: -1
                                };
                                this.pressed.shift = false;
                                this.update.selection.start()
                            }
                        }
                        return {}
                    }
                    Ī.e = Ī => {
                        class TabJF_Event {
                            dispatch(name, details = {}) {
                                details = Object.assign({
                                    instance: this
                                }, details);
                                const event = this.event.create(name, details);
                                this.editor.dispatchEvent(event);
                                return event
                            }
                            create(name, detail = {}) {
                                return new CustomEvent(name, {
                                    detail: details,
                                    bubbles: true,
                                    cancelable: true,
                                    composed: true,
                                })
                            }
                        }
                        return {}
                    }
                    Ī.f = Ī => {
                        class TabJF_Font {
                            lab = null;
                            createLab() {
                                this.font.lab = document.createElement("canvas")
                            }
                            getCssStyle(element, prop) {
                                return window.getComputedStyle(element, null).getPropertyValue(prop)
                            }
                            getCanvasFontSize(el) {
                                const fontWeight = this.font.getCssStyle(el, 'font-weight') || ;
                                const fontSize = this.font.getCssStyle(el, 'font-size') || ;
                                const fontFamily = this.font.getCssStyle(el, 'font-family') || ;
                                return `${fontWeight} ${fontSize} ${fontFamily}`
                            }
                            calculateWidth(text, el) {
                                const context = this.font.lab.getContext("2d");
                                context.font = this.font.getCanvasFontSize(el);
                                return context.measureText(text).width
                            }
                            getLetterByWidth(text, el, left) {
                                if (text.length <= 1) {
                                    const singleSize = this.font.calculateWidth(text, el);
                                    if (singleSize / 2 > left) {
                                        return 0
                                    }
                                    return 1
                                }
                                const half = text.slice(0, Math.floor(text.length / 2));
                                const textWidth = this.font.calculateWidth(half, el);
                                if (left > textWidth) {
                                    return half.length + this.font.getLetterByWidth(text.slice(Math.floor(text.length / 2)), el, left - textWidth)
                                }
                                return this.font.getLetterByWidth(half, el, left)
                            }
                        }
                        return {}
                    }
                    Ī.g = Ī => {
                        class TabJF_Get {
                            clone(obj) {
                                return JSON.parse(JSON.stringify(obj))
                            }
                            clonedPos() {
                                const pos = Object.assign({}, this.pos);
                                pos.el = this.pos.el;
                                return pos
                            }
                            myself() {
                                return this
                            }
                            visibleLines() {
                                return this.render.content.slice(this.render.hidden, this.render.hidden + this.render.linesLimit)
                            }
                            selectedNodes(sLine = null, eLine = null) {
                                if (!sLine || !eLine) {
                                    const sel = this.get.selection(),
                                        revCheck = this.selection.reverse && !this.selection.expanded;
                                    sLine = this.get.line(revCheck ? sel.focusNode : sel.anchorNode);
                                    eLine = this.get.line(revCheck ? sel.anchorNode : sel.focusNode)
                                }
                                if (!sLine || !eLine) throw new Error('Couldn\'t find lines');
                                return [sLine.cloneNode(true), ...this.get.selectedNodesRecursive(sLine.nextSibling, eLine)]
                            }
                            selectedNodesRecursive(node, end) {
                                if (node === null) throw new Error('The node doesn\'t exist in this parent');
                                if (node == end) return [node.cloneNode(true)];
                                if (node.nodeName !== ) return this.get.selectedNodesRecursive(node.nextSibling, end);
                                return [node.cloneNode(true), ...this.get.selectedNodesRecursive(node.nextSibling, end)]
                            }
                            selectedLines() {
                                const sel = this.get.selection();
                                if (sel.type != ) return;
                                let start = this.get.clone(this.selection.start);
                                let end = this.get.clone(this.selection.end);
                                if (start.line > end.line || (start.line == end.line && start.node > end.node) || (start.line == end.line && start.node == end.node && start.letter > end.letter)) {
                                    let tmp = start;
                                    start = end;
                                    end = tmp
                                }
                                if (start.line == end.line) {
                                    const line = this.get.clone(this.render.content[start.line]);
                                    delete line.ends;
                                    delete line.groupPath;
                                    if (start.node == end.node) {
                                        let content = this.replace.spaceChars(line.content[start.node].content);
                                        let text = this.replace.spaces(content.substr(start.letter, end.letter - start.letter));
                                        line.content = [this.syntax.create.span({}, text)];
                                        return [line]
                                    } else {
                                        let startNode = line.content[start.node];
                                        let endNode = line.content[end.node];
                                        startNode.content = this.replace.spaces(this.replace.spaceChars(startNode.content).substr(start.letter));
                                        endNode.content = this.replace.spaces(this.replace.spaceChars(endNode.content).substr(0, end.letter));
                                        line.content = [startNode].concat(line.content.slice(start.node + 1, end.node + 1));
                                        return [line]
                                    }
                                }
                                let linesBetween = this.render.content.slice(start.line + 1, end.line);
                                let startLine = this.get.clone(this.render.content[start.line]);
                                let endLine = this.get.clone(this.render.content[end.line]);
                                endLine.content = endLine.content.slice(0, end.node + 1);
                                let endSpan = endLine.content[endLine.content.length - 1];
                                endSpan.content = endSpan.content.replaceAll('&nbsp;', ' ');
                                endSpan.content = endSpan.content.substr(0, end.letter);
                                endSpan.content = endSpan.content.replaceAll(' ', '&nbsp;');
                                startLine.content = startLine.content.slice(start.node);
                                let startNode = startLine.content[0];
                                startNode.content = startNode.content.replaceAll('&nbsp;', ' ');
                                startNode.content = startNode.content.substr(start.letter);
                                startNode.content = startNode.content.replaceAll(' ', '&nbsp;');
                                return [startLine].concat(linesBetween, [endLine])
                            }
                            elPos(el) {
                                for (let i = 0; i < el.parentElement.children.length; i++) {
                                    if (el.parentElement.children[i] == el) return i
                                }
                                return false
                            }
                            linePos(line) {
                                let linePos = 0;
                                for (let i = 0; i < this.editor.children.length; i++) {
                                    let child = this.editor.children[i];
                                    if (line == child) return linePos;
                                    if (child.nodeName && child.nodeName == ) linePos++
                                }
                                return false
                            }
                            selection() {
                                return window.getSelection ? window.getSelection() : document.selection
                            }
                            realPos() {
                                const children = Object.values(this.pos.el.parentElement.children);
                                let letters = 0;
                                for (let i = 0; i < children.length; i++) {
                                    if (this.pos.el == children[i]) break;
                                    letters += children[i].innerText.length
                                }
                                letters += this.pos.letter;
                                return {
                                    x: letters,
                                    y: this.pos.line
                                }
                            }
                            line(el) {
                                if (!el.parentElement) return false;
                                if (el.parentElement == this.editor) return el;
                                return this.get.line(el.parentElement)
                            }
                            lineByPos(pos) {
                                pos -= this.render.hidden;
                                if (pos >= 0) {
                                    let linePos = -1;
                                    for (var i = 0; i < this.editor.children.length; i++) {
                                        let line = this.editor.children[i];
                                        if (line.nodeName == ) linePos++;
                                        if (linePos == pos) return line
                                    }
                                } else {
                                    let linePos = 0;
                                    for (var i = this.editor.children.length - 1; i > -1; i--) {
                                        let line = this.editor.children[i];
                                        if (line.nodeName == ) linePos++;
                                        if (linePos == pos * -1) return line
                                    }
                                }
                                return false
                            }
                            lineInDirection(line, dir, first = true) {
                                if (first && line?.nodeName != ) throw new Error("Parent has wrong tag, can't find proper lines");
                                if (!first && line?.nodeName == ) return line;
                                let newLine;
                                if (line === null) return line;
                                if (dir < 0) newLine = line.previousSibling;
                                else if (dir > 0) newLine = line.nextSibling;
                                if (newLine === null) return newLine;
                                if (newLine.nodeType != 1) {
                                    let newNewLine;
                                    if (dir < 0) newNewLine = newLine.previousSibling;
                                    else if (dir > 0) newNewLine = newLine.nextSibling;
                                    newLine.remove();
                                    return this.get.lineInDirection(newNewLine, dir, false)
                                }
                                if (newLine.nodeName != ) return this.get.lineInDirection(newLine, dir, false);
                                if (dir == -1 || dir == 1) return newLine;
                                return this.get.lineInDirection(newLine, dir < 0 ? dir + 1 : dir - 1, true)
                            }
                            sibling(node, dir) {
                                if (dir > 0) return node.nextSibling;
                                else if (dir < 0) return node.previousSibling
                            }
                            childIndex(el) {
                                for (var i = 0; i < el.parentElement.childNodes.length; i++) {
                                    if (el.parentElement.childNodes[i] == el) return i
                                }
                                return false
                            }
                            attributes(el) {
                                const attrsObj = {};
                                for (let att, i = 0, atts = el.attributes, n = atts.length; i < n; i++) {
                                    att = atts[i];
                                    attrsObj[att.nodeName] = att.nodeValue
                                }
                                return attrsObj
                            }
                            splitNode(pos = this.pos.letter) {
                                let text = this.pos.el.innerText;
                                return {
                                    pre: this.set.attributes(this.pos.el.attributes, text.substr(0, pos)),
                                    suf: this.set.attributes(this.pos.el.attributes, text.substr(pos))
                                }
                            }
                            splitRow(el = this.pos.el, pos = this.pos.letter) {
                                let local = this.get.splitNode(pos);
                                let nodes = this.get.nextSiblingAndRemove(el.nextSibling);
                                local.suf = [local.suf, ...nodes];
                                return local
                            }
                            nextSiblingAndRemove(el) {
                                if (el === null) return [];
                                let nodes = [];
                                let span = this.set.attributes(el.attributes, el.innerText);
                                nodes.push(span);
                                if (el.nextSibling) {
                                    let nextSpan = this.get.nextSiblingAndRemove(el.nextSibling);
                                    nodes = nodes.concat(nextSpan)
                                }
                                el.remove();
                                return nodes
                            }
                            sentence(line) {
                                let words = '';
                                line.content.forEach(span => {
                                    words += span.content
                                });
                                return this.replace.spaceChars(words)
                            }
                            words(sentence) {
                                let word = '';
                                words = [];
                                let spaces = false;
                                if (this.is.space(sentence[0])) spaces = true;
                                for (let i = 0; i < sentence.length; i++) {
                                    const letter = sentence[i];
                                    const isSpace = this.is.space(letter);
                                    if (isSpace && spaces == false || !isSpace && spaces == true) {
                                        words.push(word);
                                        word = letter
                                    } else word += letter;
                                    if (isSpace) spaces = true;
                                    else spaces = false
                                }
                                words.push(word);
                                return words
                            }
                            currentSpanContent() {
                                return this.replace.spaceChars(this.render.content[this.pos.line].content[this.pos.childIndex].content)
                            }
                            spaceIndex(text, start = 0) {
                                const char = text.indexOf('\u00A0', start);
                                if (char !== -1) {
                                    return char
                                }
                                return text.indexOf(' ', start)
                            }
                        }
                        return {}
                    }
                    Ī.h = Ī => {
                        class TabJF_Is_Line {
                            visible(line) {
                                return !(line < this.render.hidden || line > this.render.hidden + this.render.linesLimit)
                            }
                        }
                        return {}
                    }
                    Ī.i = Ī => {
                        class TabJF_Is {
                            space(word) {
                                return word == " " || word == "\u00A0" || word == '&nbsp;'
                            }
                        }
                        return {}
                    }
                    Ī.j = Ī => {
                        class TabJF_Keys {
                            enter(e = null) {
                                this.newLine(e)
                            }
                            backspace(e = null) {
                                if (!this.selection.active) {
                                    if (this.pressed.ctrl) this.remove.word(-1);
                                    else this.remove.one(-1)
                                } else {
                                    const sel = this.get.selection();
                                    if (sel.type != ) this.remove.one(-1);
                                    else this.remove.selected()
                                }
                            }
                            tab(e = null) {
                                e.preventDefault();
                                let tab = '';
                                for (let i = 0; i < this.tabWidth; i++) {
                                    tab += '&nbsp;'
                                }
                                this.insert(tab)
                            }
                            escape(e = null) {
                                this.end.select()
                            }
                            space(e = null) {
                                this.insert('&nbsp;')
                            }
                            delete(e = null) {
                                if (!this.selection.active) {
                                    if (this.pressed.ctrl) this.remove.word(1);
                                    else this.remove.one(1)
                                } else {
                                    const sel = this.get.selection();
                                    if (sel.type != ) this.remove.one(1);
                                    else this.remove.selected()
                                }
                            }
                            moveCtrl(dir, el = this.pos.el, c_pos = this.pos.letter) {
                                let newPos, text = el.innerText;
                                if (dir < 0) newPos = text.split("").reverse().indexOf('\u00A0', text.length - c_pos);
                                else if (dir > 0) newPos = text.indexOf('\u00A0', c_pos);
                                if (text.length - newPos === c_pos && dir < 0) {
                                    c_pos--
                                } else if (newPos === c_pos && dir > 0) {
                                    c_pos++
                                } else if (newPos === -1) {
                                    const prev = el.previousSibling,
                                        next = el.nextSibling;
                                    if (dir < 0 && prev) {
                                        this.keys.moveCtrl(dir, prev, prev.innerText.length);
                                        return
                                    }
                                    if (dir > 0 && next) {
                                        this.keys.moveCtrl(dir, next, 0);
                                        return
                                    }
                                    if (c_pos == 0 && this.pos.line > 0 && dir < 0 || c_pos == text.length && this.pos.line >= 0 && this.pos.line < this.render.content.length - 1 && dir > 0) {
                                        if (dir < 0) {
                                            const line = this.get.lineByPos(this.pos.line - 1);
                                            const el = line.childNodes[line.childNodes.length - 1];
                                            this.set.side(el, dir * -1, this.pos.line - 1);
                                            this.keys.moveCtrl(dir);
                                            return
                                        } else {
                                            const line = this.get.lineByPos(this.pos.line);
                                            const el = line.childNodes[0];
                                            this.set.side(el, dir * -1, this.pos.line + 1);
                                            this.keys.moveCtrl(dir);
                                            return
                                        }
                                    } else {
                                        this.set.side(el, dir)
                                    }
                                    this.pos.childIndex = this.get.childIndex(el);
                                    this.lastX = this.get.realPos().x;
                                    return
                                } else {
                                    if (dir < 0) c_pos = text.length - newPos;
                                    else if (dir > 0) c_pos = newPos
                                }
                                this.set.pos(el, c_pos, this.pos.line, this.get.childIndex(el));
                                this.lastX = this.get.realPos().x
                            }
                            move(dirX, dirY, recursionCheck = false) {
                                if (this.get.selection().type == ) {
                                    this.caret.refocus(this.selection.end.letter, this.selection.end.line, this.selection.end.node)
                                }
                                const oldLine = this.pos.line;
                                if (this.selection.active && !this.pressed.shift) {
                                    if (this.selection.reverse && !this.selection.expanded && dirX < 0) dirX = 0;
                                    else if (dirX > 0) dirX = 0
                                }
                                if (this.pressed.ctrl && dirX != 0) this.keys.moveCtrl(dirX);
                                else if (dirX != 0) this.keys.moveX(dirY, dirX);
                                if (dirY != 0) this.keys.moveY(dirY, dirX);
                                if (this.pos.el.innerText.length == 0 && (this.pos.el.previousSibling && dirX < 0 || this.pos.el.nextSibling && dirX > 0) && !recursionCheck) {
                                    let temp = this.pos.el;
                                    this.keys.move(dirX, 0, true);
                                    temp.remove()
                                }
                                if (this.pressed.shift) {
                                    this.update.selection.end();
                                    this.checkSelect()
                                } else this.end.select()
                            }
                            moveX(dirY, dirX) {
                                let el = this.pos.el,
                                    prev = el.previousSibling;
                                if (this.pos.letter + dirX <= -1) {
                                    if (prev && prev.nodeType == 1) {
                                        this.pos.el = prev;
                                        this.pos.childIndex--;
                                        this.pos.letter = prev.innerText.length
                                    } else {
                                        let previousLine = this.get.lineInDirection(el.parentElement, -1);
                                        if (!previousLine) return;
                                        this.pos.el = previousLine.children[previousLine.children.length - 1];
                                        this.pos.childIndex = previousLine.children.length - 1;
                                        this.caret.setByChar(this.pos.el.innerText.length, this.pos.line - 1);
                                        this.lastX = this.get.realPos().x;
                                        this.caret.scrollToX();
                                        return
                                    }
                                } else if (this.pos.letter + dirX > el.innerText.length && el.nextSibling && el.nextSibling.nodeType == 1) {
                                    this.pos.el = el.nextSibling;
                                    this.pos.letter = 0;
                                    this.pos.childIndex++
                                } else if (this.pos.letter + dirX > el.innerText.length) {
                                    let nextLine = this.get.lineInDirection(el.parentElement, 1);
                                    if (!nextLine) return;
                                    this.pos.el = nextLine.children[0];
                                    this.pos.childIndex = 0;
                                    this.caret.setByChar(0, this.pos.line + 1);
                                    this.lastX = this.get.realPos().x;
                                    this.caret.scrollToX();
                                    return
                                }
                                this.caret.setByChar(this.pos.letter + dirX, this.pos.line);
                                this.lastX = this.get.realPos().x;
                                this.caret.scrollToX()
                            }
                            moveY(dirY, dirX) {
                                const line = this.pos.line;
                                if (line + dirY <= -1) return;
                                if (line + dirY >= this.render.content.length) return;
                                let realLetters = this.get.realPos().x;
                                this.pos.line = line + dirY;
                                let newLine = this.get.lineByPos(line + dirY);
                                if (!newLine) return;
                                if (newLine.innerText.length < realLetters + dirX) {
                                    this.pos.childIndex = newLine.children.length - 1;
                                    this.pos.letter = newLine.children[this.pos.childIndex].innerText.length
                                } else {
                                    let currentLetterCount = 0;
                                    for (let i = 0; i < newLine.children.length; i++) {
                                        let child = newLine.children[i];
                                        currentLetterCount += child.innerText.length;
                                        if (currentLetterCount >= this.lastX) {
                                            this.pos.childIndex = this.get.childIndex(child);
                                            this.pos.letter = this.lastX - (currentLetterCount - child.innerText.length);
                                            break
                                        } else if (i + 1 == newLine.children.length) {
                                            this.pos.childIndex = newLine.children.length - 1;
                                            this.pos.letter = child.innerText.length
                                        }
                                    }
                                }
                                if (dirY > 0 && this.pos.line + dirY + 3 >= this.render.linesLimit + this.render.hidden) {
                                    this.render.move.overflow(0, this.settings.line)
                                } else if (dirY < 0 && this.pos.line + dirY <= this.render.hidden) {
                                    this.render.move.overflow(0, -this.settings.line)
                                }
                                this.caret.refocus()
                            }
                        }
                        return {}
                    }
                    Ī.k = Ī => {
                        class TabJF_Remove {
                            selected() {
                                let start = this.get.clone(this.selection.start);
                                let end = this.get.clone(this.selection.end);
                                if (start.line > end.line || (start.line == end.line && start.node > end.node) || (start.line == end.line && start.node == end.node && start.letter > end.letter)) {
                                    let tmp = start;
                                    start = end;
                                    end = tmp
                                }
                                const sel = this.get.selection();
                                if (sel.type != ) return;
                                if (start.line == end.line) {
                                    if (start.node == end.node) {
                                        let content = this.replace.spaceChars(this.render.content[start.line].content[start.node].content);
                                        let pre = this.replace.spaces(content.substr(0, start.letter));
                                        let suf = this.replace.spaces(content.substr(end.letter));
                                        this.render.content[start.line].content[start.node].content = pre + suf
                                    } else {
                                        let startNode = this.render.content[start.line].content[start.node];
                                        let endNode = this.render.content[end.line].content[end.node];
                                        startNode.content = this.replace.spaces(this.replace.spaceChars(startNode.content).substr(0, start.letter));
                                        endNode.content = this.replace.spaces(this.replace.spaceChars(endNode.content).substr(end.letter));
                                        if (endNode.content.length == 0) end.node++;
                                        this.render.content[start.line].content.splice(start.node + 1, end.node - (start.node + 1))
                                    }
                                } else {
                                    let startLine = this.render.content[start.line];
                                    startLine.content = startLine.content.slice(0, start.node + 1);
                                    let startSpan = startLine.content[start.node];
                                    startSpan.content = this.replace.spaceChars(startSpan.content).substr(0, start.letter);
                                    startSpan.content = this.replace.spaces(startSpan.content);
                                    let endLine = this.render.content[end.line];
                                    endLine.content = endLine.content.slice(end.node);
                                    let endSpan = endLine.content[0];
                                    endSpan.content = this.replace.spaceChars(endSpan.content).substr(end.letter);
                                    endSpan.content = this.replace.spaces(endSpan.content);
                                    if (endSpan.content.length > 0 || endLine.content.length > 0) startLine.content = startLine.content.concat(endLine.content);
                                    this.render.content.splice(start.line + 1, end.line - start.line);
                                    this.render.update.minHeight();
                                    this.render.update.scrollWidth()
                                }
                                this.caret.refocus(start.letter, start.line, start.node, );
                                this.lastX = this.get.realPos().x;
                                this.render.move.page();
                                this.end.select()
                            }
                            word(dir, childIndex = this.pos.childIndex, c_pos = this.pos.letter) {
                                const text = this.get.currentSpanContent();
                                const spanLength = text.length;
                                const letter = this.pos.letter;
                                const line = this.render.content[this.pos.line];
                                let pos = {
                                    letter: this.pos.letter,
                                    childIndex: this.pos.childIndex,
                                    text: ''
                                };
                                if (letter == 0 && this.pos.childIndex == 0 && dir < 0 || letter == text.length && this.pos.childIndex == line.content.length - 1 && dir > 0) {
                                    this.mergeLine(dir);
                                    return
                                }
                                let nextSymbol = '';
                                if (dir < 0) {
                                    if (letter == 0) {
                                        const previous = this.replace.spaceChars(line.content[this.pos.childIndex - 1].content);
                                        nextSymbol = previous[previous.length - 1]
                                    } else {
                                        nextSymbol = this.replace.spaceChars(text[letter - 1])
                                    }
                                } else if (dir > 0) {
                                    if (letter == text.length) {
                                        const next = this.replace.spaceChars(line.content[this.pos.childIndex + 1].content);
                                        nextSymbol = next[0]
                                    } else {
                                        nextSymbol = this.replace.spaceChars(text[letter])
                                    }
                                }
                                if (this.is.space(nextSymbol)) {
                                    this.remove.one(dir);
                                    return
                                }
                                if (dir < 0) {
                                    for (let i = this.pos.childIndex; i >= 0; i--) {
                                        const textSpan = this.replace.spaceChars(line.content[i].content);
                                        let index = this.get.spaceIndex(textSpan.split("").reverse(), textSpan.length - pos.letter);
                                        if (index != -1) {
                                            pos.letter = textSpan.length - index;
                                            pos.text = textSpan;
                                            break
                                        }
                                        if (i != 0) {
                                            pos.childIndex--;
                                            pos.letter = this.replace.spaceChars(line.content[pos.childIndex].content).length
                                        }
                                    }
                                    line.content[this.pos.childIndex].content = text.substr(letter);
                                    line.content.splice(pos.childIndex + 1, this.pos.childIndex - pos.childIndex - 1);
                                    line.content[pos.childIndex].content = pos.text.substr(0, pos.letter);
                                    this.pos.childIndex = pos.childIndex;
                                    this.pos.letter = pos.letter;
                                    if (line.content[this.pos.childIndex].content.length == 0) {
                                        this.pos.letter = 0
                                    }
                                } else if (dir > 0) {
                                    for (let i = this.pos.childIndex; i < line.content.length; i++) {
                                        const textSpan = this.replace.spaceChars(line.content[i].content);
                                        let index = this.get.spaceIndex(textSpan, pos.letter);
                                        if (index != -1) {
                                            pos.letter = index;
                                            pos.text = textSpan;
                                            break
                                        }
                                        if (i + 1 !== line.content.length) {
                                            pos.childIndex++;
                                            pos.letter = 0
                                        }
                                    }
                                    line.content[pos.childIndex].content = pos.text.substr(0, pos.letter + 1);
                                    line.content.splice(this.pos.childIndex + 1, pos.childIndex - this.pos.childIndex - 1);
                                    line.content[this.pos.childIndex].content = text.substr(0, this.pos.letter)
                                }
                                this.caret.refocus();
                                this.lastX = this.get.realPos().x
                            }
                            one(dir) {
                                const text = this.get.currentSpanContent();
                                const spanLength = text.length;
                                const letter = this.pos.letter;
                                const line = this.render.content[this.pos.line];
                                if (letter == 0 && this.pos.childIndex == 0 && dir < 0 || letter == text.length && this.pos.childIndex == line.content.length - 1 && dir > 0) {
                                    this.mergeLine(dir);
                                    return
                                }
                                if (dir > 0) {
                                    if (text.length == 1 && letter == 0) {
                                        if (line.content.length != 1) {
                                            line.content.splice(this.pos.childIndex, 1)
                                        } else {
                                            this.update.currentSpanContent('')
                                        }
                                        this.pos.letter = 0
                                    } else if (text.length == letter) {
                                        this.pos.childIndex++;
                                        this.pos.letter = 0;
                                        this.remove.one(dir);
                                        return
                                    } else {
                                        const pre = text.substr(0, letter);
                                        const suf = text.substr(letter + 1);
                                        this.update.currentSpanContent(pre + suf)
                                    }
                                } else if (dir < 0) {
                                    if (text.length == 1 && letter == 1) {
                                        if (line.content.length != 1) {
                                            line.content.splice(this.pos.childIndex, 1)
                                        } else {
                                            this.update.currentSpanContent('')
                                        }
                                        if (this.pos.childIndex == 0) {
                                            this.pos.letter = 0
                                        } else {
                                            this.pos.childIndex--;
                                            this.pos.letter = this.replace.spaceChars(line.content[this.pos.childIndex].content).length
                                        }
                                    } else if (letter == 0) {
                                        this.pos.childIndex--;
                                        this.pos.letter = this.replace.spaceChars(line.content[this.pos.childIndex].content).length;
                                        this.remove.one(dir);
                                        return
                                    } else {
                                        const pre = text.substr(0, letter - 1);
                                        const suf = text.substr(letter);
                                        this.pos.letter--;
                                        this.update.currentSpanContent(pre + suf)
                                    }
                                    this.caret.refocus();
                                    this.lastX = this.get.realPos().x
                                }
                            }
                        }
                        return {}
                    }
                    Ī.l = Ī => {
                        class TabJF_Render_Add {
                            line(line, pos) {
                                this.render.content.splice(pos, 0, this.truck.exportLine(line));
                                this.render.fill.event();
                                this.render.update.minHeight()
                            }
                        }
                        return {}
                    }
                    Ī.m = Ī => {
                        class TabJF_Render_Fill {
                            event(e = null) {
                                try {
                                    const selection = this.get.selection();
                                    let top = this.render.overflow.scrollTop;
                                    let startLine = Math.floor(top / this.settings.line);
                                    this.render.move.page({
                                        offset: startLine,
                                        clear: false,
                                        refocus: false
                                    })
                                };
                                catch (e); {};
                                finally {
                                    this.update.page()
                                }
                            }
                        }
                        return {}
                    }
                    Ī.n = Ī => {
                        class TabJF_Render_Move {
                            page(obj = {}) {
                                const required = {
                                    offset: this.render.hidden,
                                    clear: true,
                                    reverse: false,
                                    refocus: true
                                };
                                Object.keys(required).forEach(attr => {
                                    obj[attr] = typeof obj[attr]. == .
                                    'undefined' ? required[attr]: obj[attr]
                                });
                                let offset = obj.offset,
                                    clear = obj.clear,
                                    reverse = obj.reverse,
                                    refocus = obj.refocus;
                                this.truck.import(this.render.content, this.render.linesLimit, offset, clear, reverse);
                                this.render.hidden = offset;
                                this.editor.style.setProperty('--paddingTop', this.render.hidden * this.settings.line);
                                this.editor.style.setProperty('--counter-current', this.render.hidden);
                                if (refocus && this.caret.isVisible()) this.caret.refocus()
                            }
                            overflow(x, y) {
                                let top = this.render.overflow.scrollTop;
                                let left = this.render.overflow.scrollLeft;
                                this.render.overflow.scrollTo(left + x, top + y)
                            }
                        }
                        return {}
                    }
                    Ī.o = Ī => {
                        class TabJF_Render_Remove {
                            line(pos) {
                                this.render.content.splice(pos, 1);
                                this.render.fill.event();
                                this.render.update.minHeight()
                            }
                        }
                        return {}
                    }
                    Ī.p = Ī => {
                        class TabJF_Render_Set {
                            overflow(x = null, y = null) {
                                if (x === null) x = this.render.overflow.scrollLeft;
                                if (y === null) y = this.render.overflow.scrollTop;
                                this.render.overflow.scrollTo(x, y)
                            }
                        }
                        return {}
                    }
                    Ī.r = Ī => {
                        class TabJF_Render_Update {
                            minHeight(lines = this.render.content.length) {
                                lines = lines < this.render.linesLimit ? this.render.linesLimit : lines;
                                this.editor.style.setProperty("--min-height", this.settings.line * lines)
                            }
                            scrollWidth() {
                                this.render.maxLineWidth = 0;
                                const p = document.createElement('p');
                                this.editor.appendChild(p);
                                this.render.content.forEach((line, i) => {
                                    this.render.update.scrollWidthWithLine(p, line, i)
                                });
                                p.remove();
                                this.editor.style.setProperty("--scroll-width", this.render.maxLineWidth + this.settings.left)
                            }
                            scrollWidthWithLine(lineEl, lineContent, lineIndex) {
                                let text = '';
                                lineContent.content.forEach(item => {
                                    text += item.content
                                });
                                const width = this.font.calculateWidth(text, lineEl);
                                if (this.render.maxLineWidth < width) {
                                    this.render.maxLineWidth = width;
                                    this.render.maxLine = lineIndex
                                }
                            }
                            scrollWidthWithCurrentLine() {
                                const line = this.render.content[this.pos.line];
                                if (!line) {
                                    return
                                }
                                const lineEl = this.get.lineByPos(this.pos.line);
                                if (!lineEl) return;
                                const width = this.render.update.scrollWidthWithLine(lineEl, line, this.pos.line);
                                this.editor.style.setProperty("--scroll-width", width + this.settings.left)
                            }
                        }
                        return {}
                    }
                    Ī.s = Ī => {
                        class TabJF_Render {
                            hidden = 0;
                            content = null;
                            linesLimit = 80;
                            maxLineWidth = 0;
                            overflow = null;
                            focusLost = false;
                            removeScroll() {
                                this.render.overflow.removeEventListener('scroll', this.render.fill.event, true)
                            }
                            init(importObj = false, contentText = false) {
                                if (importObj) this.render.content = importObj;
                                else if (contentText) this.render.content = this.truck.exportText(contentText);
                                else this.render.content = this.truck.export();
                                this.clear.editor(false);
                                this.render.linesLimit = Math.ceil(this.settings.height / this.settings.line) + 2;
                                const overflow = document.createElement("div");
                                overflow.addEventListener('scroll', this.render.fill.event, true);
                                overflow.className = "tabjf_editor-con";
                                this.render.update.minHeight();
                                this.render.update.scrollWidth();
                                this.editor.parentElement.insertBefore(overflow, this.editor);
                                overflow.appendChild(this.editor);
                                this.render.overflow = overflow
                            }
                        }
                        return {}
                    }
                    Ī.t = Ī => {
                        class TabJF_Replace {
                            spaces(string) {
                                if (string.length != 0) {
                                    string = string.replaceAll(' ', '&nbsp;').replaceAll(this.spaceUChar, )
                                }
                                return string
                            }
                            spaceChars(string) {
                                return string.replaceAll('&nbsp;', ' ')
                            }
                        }
                        return {}
                    }
                    Ī.u = Ī => {
                        class TabJF_Set {
                            docEvents() {
                                if (this.docEventsSet) return;
                                document.addEventListener('paste', this.catchClipboard.bind(this));
                                document.addEventListener('keydown', this.key.bind(this));
                                document.addEventListener('keyup', this.key.bind(this));
                                window.addEventListener('resize', this.update.resizeDebounce.bind(this));
                                this.docEventsSet = true
                            }
                            preciseMethodsProxy(scope, path) {
                                if (path.length == 1) scope[path[0]] = new Proxy(scope[path[0]], this._proxySaveHandle);
                                else {
                                    this.set.preciseMethodsProxy(scope[path[0]], path.slice(1))
                                }
                            }
                            side(node, dirX, newLine = this.pos.line, childIndex = this.pos.childIndex) {
                                let letter = this.pos.letter;
                                this.pos.childIndex = childIndex;
                                this.pos.el = node;
                                if (dirX > 0) letter = node.innerText.length;
                                else if (dirX < 0) letter = 0;
                                this.caret.setByChar(letter, newLine)
                            }
                            pos(node, letter, line, childIndex) {
                                this.pos.childIndex = childIndex;
                                this.pos.letter = letter;
                                this.pos.line = line;
                                this.caret.setByChar(letter, line, node)
                            }
                            attributes(attributes, text) {
                                let newSpan = document.createElement("span");
                                for (let att, i = 0, atts = attributes, n = atts.length; i < n; i++) {
                                    att = atts[i];
                                    newSpan.setAttribute(att.nodeName, att.nodeValue)
                                }
                                newSpan.innerHTML = text;
                                return newSpan
                            }
                            attributesFromContent(attributes, text) {
                                if (!attributes) {
                                    return
                                }
                                let newSpan = document.createElement("span");
                                Object.keys(attributes).forEach(name => {
                                    newSpan.setAttribute(name, attributes[name])
                                });
                                newSpan.innerHTML = text;
                                return newSpan
                            }
                        }
                        return {}
                    }
                    Ī.w = Ī => {
                        class TabJF_Syntax_Create {
                            span(attrs, text) {
                                return {
                                    attrs,
                                    content: this.replace.spaces(text)
                                }
                            }
                        }
                        return {}
                    }
                    Ī.z = Ī => {
                        let fonts;
                        return {
                            fonts: {
                                serif: true,
                                "sans-serif": true,
                                monospace: true,
                                cursive: true,
                                fantasy: true,
                                "system-ui": true,
                                "ui-serif": true,
                                "ui-sans-serif": true,
                                "ui-monospace": true,
                                "ui-rounded": true,
                                emoji: true,
                                math: true,
                                fangsong: true,
                                "Abadi MT Condensed Light": true,
                                Aharoni: true,
                                "Aharoni Bold": true,
                                Aldhabi: true,
                                "AlternateGothic2 BT": true,
                                "Andale Mono": true,
                                Andalus: true,
                                "Angsana New": true,
                                AngsanaUPC: true,
                                Aparajita: true,
                                "Apple Chancery": true,
                                "Arabic Typesetting": true,
                                Arial: true,
                                "Arial Black": true,
                                "Arial narrow": true,
                                "Arial Nova": true,
                                "Arial Rounded MT Bold": true,
                                Arnoldboecklin: true,
                                "Avanta Garde": true,
                                Bahnschrift: true,
                                "Bahnschrift Light": true,
                                "Bahnschrift SemiBold": true,
                                "Bahnschrift SemiLight": true,
                                Baskerville: true,
                                Batang: true,
                                BatangChe: true,
                                "Big Caslon": true,
                                "BIZ UDGothic": true,
                                "BIZ UDMincho Medium": true,
                                Blippo: true,
                                "Bodoni MT": true,
                                "Book Antiqua": true,
                                Bookman: true,
                                "Bradley Hand": true,
                                "Browallia New": true,
                                BrowalliaUPC: true,
                                "Brush Script MT": true,
                                "Brush Script Std": true,
                                Brushstroke: true,
                                Calibri: true,
                                "Calibri Light": true,
                                "Calisto MT": true,
                                Cambodian: true,
                                Cambria: true,
                                "Cambria Math": true,
                                Candara: true,
                                "Century Gothic": true,
                                Chalkduster: true,
                                Cherokee: true,
                                "Comic Sans": true,
                                "Comic Sans MS": true,
                                Consolas: true,
                                Constantia: true,
                                Copperplate: true,
                                "Copperplate Gothic Light": true,
                                "Copperplate Gothic Bold": true,
                                Corbel: true,
                                "Cordia New": true,
                                CordiaUPC: true,
                                Coronetscript: true,
                                Courier: true,
                                "Courier New": true,
                                DaunPenh: true,
                                David: true,
                                DengXian: true,
                                "DFKai-SB": true,
                                Didot: true,
                                DilleniaUPC: true,
                                DokChampa: true,
                                Dotum: true,
                                DotumChe: true,
                                Ebrima: true,
                                "Estrangelo Edessa": true,
                                EucrosiaUPC: true,
                                Euphemia: true,
                                FangSong: true,
                                Florence: true,
                                "Franklin Gothic Medium": true,
                                FrankRuehl: true,
                                FreesiaUPC: true,
                                Futara: true,
                                Gabriola: true,
                                Gadugi: true,
                                Garamond: true,
                                Gautami: true,
                                Geneva: true,
                                Georgia: true,
                                "Georgia Pro": true,
                                "Gill Sans": true,
                                "Gill Sans Nova": true,
                                Gisha: true,
                                "Goudy Old Style": true,
                                Gulim: true,
                                GulimChe: true,
                                Gungsuh: true,
                                GungsuhChe: true,
                                Hebrew: true,
                                "Hoefler Text": true,
                                "HoloLens MDL2 Assets": true,
                                Impact: true,
                                "Ink Free": true,
                                IrisUPC: true,
                                "Iskoola Pota": true,
                                Japanese: true,
                                JasmineUPC: true,
                                "Javanese Text": true,
                                "Jazz LET": true,
                                KaiTi: true,
                                Kalinga: true,
                                Kartika: true,
                                "Khmer UI": true,
                                KodchiangUPC: true,
                                Kokila: true,
                                Korean: true,
                                Lao: true,
                                "Lao UI": true,
                                Latha: true,
                                Leelawadee: true,
                                "Leelawadee UI": true,
                                "Leelawadee UI Semilight": true,
                                "Levenim MT": true,
                                LilyUPC: true,
                                "Lucida Bright": true,
                                "Lucida Console": true,
                                "Lucida Handwriting": true,
                                "Lucida Sans": true,
                                "Lucida Sans Typewriter": true,
                                "Lucida Sans Unicode": true,
                                Lucidatypewriter: true,
                                Luminari: true,
                                "Malgun Gothic": true,
                                "Malgun Gothic Semilight": true,
                                Mangal: true,
                                "Marker Felt": true,
                                Marlett: true,
                                Meiryo: true,
                                "Meiryo UI": true,
                                "Microsoft Himalaya": true,
                                "Microsoft JhengHei": true,
                                "Microsoft JhengHei UI": true,
                                "Microsoft New Tai Lue": true,
                                "Microsoft PhagsPa": true,
                                "Microsoft Sans Serif": true,
                                "Microsoft Tai Le": true,
                                "Microsoft Uighur": true,
                                "Microsoft YaHei": true,
                                "Microsoft YaHei UI": true,
                                "Microsoft Yi Baiti": true,
                                MingLiU: true,
                                MingLiU_HKSCS: true,
                                "MingLiU_HKSCS-ExtB": true,
                                "MingLiU-ExtB": true,
                                Miriam: true,
                                Monaco: true,
                                "Mongolian Baiti": true,
                                MoolBoran: true,
                                "MS Gothic": true,
                                "MS Mincho": true,
                                "MS PGothic": true,
                                "MS PMincho": true,
                                "MS UI Gothic": true,
                                "MV Boli": true,
                                "Myanmar Text": true,
                                Narkisim: true,
                                "Neue Haas Grotesk Text Pro": true,
                                "New Century Schoolbook": true,
                                "News Gothic MT": true,
                                "Nirmala UI": true,
                                "No automatic language associations": true,
                                Noto: true,
                                NSimSun: true,
                                Nyala: true,
                                Oldtown: true,
                                Optima: true,
                                Palatino: true,
                                "Palatino Linotype": true,
                                papyrus: true,
                                Parkavenue: true,
                                Perpetua: true,
                                "Plantagenet Cherokee": true,
                                PMingLiU: true,
                                Raavi: true,
                                Rockwell: true,
                                "Rockwell Extra Bold": true,
                                "Rockwell Nova": true,
                                "Rockwell Nova Cond": true,
                                "Rockwell Nova Extra Bold": true,
                                Rod: true,
                                "Sakkal Majalla": true,
                                "Sanskrit Text": true,
                                "Segoe MDL2 Assets": true,
                                "Segoe Print": true,
                                "Segoe Script": true,
                                "Segoe UI": true,
                                "Segoe UI Emoji": true,
                                "Segoe UI Historic": true,
                                "Segoe UI Symbol": true,
                                "Shonar Bangla": true,
                                Shruti: true,
                                SimHei: true,
                                SimKai: true,
                                "Simplified Arabic": true,
                                "Simplified Chinese": true,
                                SimSun: true,
                                "SimSun-ExtB": true,
                                Sitka: true,
                                "Snell Roundhan": true,
                                "Stencil Std": true,
                                Sylfaen: true,
                                Symbol: true,
                                Tahoma: true,
                                Thai: true,
                                "Times New Roman": true,
                                "Traditional Arabic": true,
                                "Traditional Chinese": true,
                                Trattatello: true,
                                "Trebuchet MS": true,
                                Tunga: true,
                                "UD Digi Kyokasho": true,
                                "UD Digi KyoKasho NK-R": true,
                                "UD Digi KyoKasho NP-R": true,
                                "UD Digi KyoKasho N-R": true,
                                "Urdu Typesetting": true,
                                "URW Chancery": true,
                                Utsaah: true,
                                Vani: true,
                                Verdana: true,
                                "Verdana Pro": true,
                                Vijaya: true,
                                Vrinda: true,
                                Webdings: true,
                                Westminster: true,
                                Wingdings: true,
                                "Yu Gothic": true,
                                "Yu Gothic UI": true,
                                "Yu Mincho": true,
                                "Zapf Chancery": true
                            }
                        }
                    }
                    Ī.q = Ī => {
                        class TabJF_Truck {
                            export (html = null) {
                                const exportAr = [];
                                if (!html) html = this.editor.children;
                                Object.values(html).forEach(function(p) {
                                    let line = this.truck.exportLine(p);
                                    if (line) {
                                        exportAr.push(line)
                                    }
                                }, this);
                                return exportAr
                            }
                            exportLine(p) {
                                if (p.nodeName !== ) return false;
                                const lineContent = [];
                                Object.values(p.children).forEach(span => {
                                    lineContent.push({
                                        attrs: this.get.attributes(span),
                                        content: this.replace.spaces(span.innerText)
                                    })
                                });
                                if (lineContent.length == 0) {
                                    lineContent.push({
                                        attrs: {},
                                        content: ''
                                    })
                                }
                                return {
                                    content: lineContent
                                }
                            }
                            exportText(text) {
                                const content = text.split('\n');
                                const conAr = [];
                                content.forEach(text => {
                                    conAr.push({
                                        content: [{
                                            attrs: {},
                                            content: this.replace.spaces(text)
                                        }]
                                    })
                                });
                                return conAr
                            }
                            import (importAr, limit = false, offset = 0, clear = true, reverse = false, container = null, replaceContent = true) {
                                if (clear && !container) this.clear.editor();
                                if (!container) container = this.editor;
                                let firstLine;
                                for (let i = offset; i < importAr.length; i++) {
                                    if (limit && i === limit + offset) break;
                                    const line = importAr[i];
                                    const lineNode = document.createElement("p");
                                    line.content.forEach((span, i) => {
                                        span.content = this.replace.spaces(span.content);
                                        const spanNode = this.set.attributesFromContent(span.attrs, span.content);
                                        if (!spanNode?.childNodes) {
                                            console.log(span.attrs, span.content)
                                        }
                                        if (spanNode.childNodes.length == 0) spanNode.appendChild(document.createTextNode(''));
                                        lineNode.appendChild(spanNode)
                                    });
                                    if (reverse) {
                                        if (!firstLine) firstLine = this.get.lineByPos(0);
                                        container.insertBefore(lineNode, firstLine)
                                    } else container.appendChild(lineNode)
                                }
                                if (replaceContent) this.render.content = importAr
                            }
                        }
                        return {}
                    }
                    Ī.v = Ī => {
                        class TabJF_Update_Selection {
                            start(letter = this.pos.letter, line = this.pos.line, index = this.pos.childIndex) {
                                const start = this.selection.start;
                                start.letter = letter;
                                start.line = line;
                                start.node = index
                            }
                            end(letter = this.pos.letter, line = this.pos.line, index = this.pos.childIndex) {
                                const end = this.selection.end;
                                end.letter = letter;
                                end.line = line;
                                end.node = index
                            }
                        }
                        return {}
                    }
                    Ī.µ = Ī => {
                        class TabJF_Update {
                            resizeDebounce = null;
                            page() {
                                if (this.settings.syntax) this.syntax.update();
                                this.render.move.page({
                                    refocus: false
                                });
                                this.checkSelect();
                                if (this.caret.isVisible()) {
                                    this.caret.recalculatePos();
                                    this.caret.refocus()
                                }
                            }
                            select() {
                                this.selection.update = true;
                                const selection = this.get.selection();
                                if (selection.type !== ) return;
                                this.selection.active = true;
                                if (selection.focusNode == this.editor) return;
                                this.selection.end = {
                                    line: this.get.linePos(this.get.line(selection.focusNode)) + this.render.hidden,
                                    node: this.get.childIndex(selection.focusNode.parentElement),
                                    letter: selection.focusOffset
                                }
                            }
                            specialKeys(e) {
                                if (!e.altKey) {
                                    this.pressed.ctrl = e.ctrlKey
                                } else {
                                    this.pressed.ctrl = false
                                }
                                const type = this.get.selection().type;
                                if (!this.pressed.shift && e.shiftKey && type != ) {
                                    this.selection.active = true;
                                    this.update.selection.start()
                                } else if (!e.shiftKey && type != ) {
                                    this.selection.active = false
                                }
                                this.pressed.shift = e.shiftKey;
                                this.pressed.alt = e.altKey
                            }
                            currentSpanContent(text) {
                                this.render.content[this.pos.line].content[this.pos.childIndex].content = this.replace.spaces(text)
                            }
                            resize(e = null) {
                                this.settings.height = this.render.overflow.offsetHeight;
                                this.render.linesLimit = Math.ceil(this.settings.height / this.settings.line) + 2;
                                this.render.update.minHeight();
                                this.render.update.scrollWidth();
                                this.clear.editor(false);
                                this.render.fill.event()
                            }
                        }
                        return {}
                    }
                    Ī.ê = Ī => {
                        class TabJF_Hidden {
                            debounce(func, timeout = 300) {
                                let timer;
                                return ...args => {
                                    clearTimeout(timer);
                                    if (args[0]. === .
                                        "clear") {
                                        return
                                    }
                                    timer = setTimeout(() => {
                                        func.apply(this, args)
                                    }, timeout)
                                }
                            }
                        }
                        return {}
                    }
                    Ī.õ = Ī => {
                        class TabJF_Save_Content {
                            remove(remove) {
                                this.render.content.splice(remove.sLine, remove.len)
                            }
                            add(add) {
                                const positions = Object.keys(add);
                                positions.forEach(linePos => {
                                    this.render.content.splice(linePos, 0, add[linePos])
                                })
                            }
                        }
                        return {}
                    }
                    Ī.ú = Ī => {
                        class TabJF_Save_Set {
                            focus() {
                                return {
                                    letter: this.pos.letter,
                                    line: this.pos.line,
                                    childIndex: this.get.childIndex(this.pos.el),
                                    topLine: this.render.hidden,
                                    lastX: this.get.realPos().x
                                }
                            }
                            add(name, args) {
                                let modifiers = 0;
                                if (name == "mergeLine") modifiers = args[0];
                                const tmp = this.get.clone(this._save.tmpDefault);
                                const sel = this.get.selection();
                                if (sel.type.toLowerCase() == ) {
                                    const start = this.selection.start;
                                    const end = this.selection.end;
                                    const startLinePos = start.line > end.line ? end.line : start.line;
                                    const endLinePos = start.line > end.line ? start.line : end.line;
                                    for (let i = startLinePos; i <= endLinePos; i++) {
                                        tmp.add[i] = this.get.clone(this.render.content[i])
                                    }
                                }
                                tmp.fun_name = name;
                                tmp.focus = this._save.set.focus();
                                const linePos = this.pos.line;
                                const line = this.get.lineByPos(linePos);
                                if (!tmp.add[linePos]) tmp.add[linePos] = this.get.clone(this.render.content[linePos]);
                                if (modifiers != 0 && !tmp.add[linePos + modifiers]) {
                                    let nexLine = this.get.lineInDirection(line, modifiers);
                                    if (nexLine) tmp.add[linePos + modifiers] = this.get.clone(this.render.content[linePos + modifiers])
                                }
                                this._save.tmp.push(tmp)
                            }
                            remove(name, args, step, startLine) {
                                const save = this._save;
                                const pos = this.pos.line;
                                if (((name == "one" || name == "word") && save.methodsStack[save.methodsStack.length - 1]. == .
                                        "mergeLine") || (name == "mergeLine" && save.methodsStack[save.methodsStack.length - 2]. == .
                                        "selected")) {
                                    save.tmp.splice(step, 1);
                                    return
                                }
                                if (name == "paste") {
                                    let tmp = save.tmp[step];
                                    tmp.remove = {
                                        sLine: startLine,
                                        len: pos - startLine + 1
                                    };
                                    tmp.focusAfter = this._save.set.focus();
                                    for (let i = tmp.remove.sLine; i < tmp.remove.sLine + tmp.remove.len; i++) {
                                        tmp.after[i] = this.get.clone(this.render.content[i])
                                    }
                                    save.tmp = [tmp];
                                    return
                                }
                                let tmp = save.tmp[step];
                                if (!tmp) tmp = save.pending[step];
                                tmp.fun_name = name;
                                const lines = Object.keys(tmp.add);
                                if (lines.length == 0) return;
                                let minOrMax = pos;
                                let max = Math.max(...lines);
                                let min = Math.max(...lines);
                                if (minOrMax < min) {
                                    min = minOrMax;
                                    max = pos
                                } else if (minOrMax > max) {
                                    max = minOrMax;
                                    min = pos
                                } else {
                                    max = minOrMax;
                                    min = minOrMax
                                }
                                tmp.remove.sLine = min;
                                tmp.remove.len = max - min + 1;
                                if (name == "newLine") {
                                    tmp.remove.sLine--;
                                    tmp.remove.len++
                                }
                                for (let i = tmp.remove.sLine; i < tmp.remove.sLine + tmp.remove.len; i++) {
                                    tmp.after[i] = this.get.clone(this.render.content[i])
                                }
                                tmp.focusAfter = save.set.focus()
                            }
                        }
                        return {}
                    }
                    Ī.A = Ī => {
                        class TabJF_Save {
                            debounce = undefined;
                            version = 0;
                            tmpDefault = {
                                fun_name: false,
                                remove: {
                                    sLine: -1,
                                    len: -1
                                },
                                after: {},
                                add: {},
                                focus: {
                                    topLine: 0,
                                    letter: -1,
                                    line: -1,
                                    childIndex: -1
                                },
                                focusAfter: {
                                    topLine: 0,
                                    letter: -1,
                                    line: -1,
                                    childIndex: -1
                                }
                            };
                            tmp = [];
                            pending = [];
                            versions = [];
                            methodsStack = [];
                            inProgress = false;
                            set = {};
                            content = {};
                            maxVersionCount = 100;
                            moveToPending() {
                                this._save.pending = this._save.pending.concat(this._save.tmp);
                                this._save.resetTmp()
                            }
                            publish() {
                                const save = this._save;
                                if (save.version > 0) {
                                    save.versions.splice(0, save.version);
                                    save.version = 0
                                }
                                if (save.pending.length == 0) return;
                                save.squash();
                                save.pending[0].focus.topLine = this.render.hidden;
                                save.versions.unshift(save.pending.reverse());
                                save.pending = [];
                                if (save.versions.length > save.maxVersionCount) {
                                    save.versions.splice(save.maxVersionCount)
                                }
                            }
                            squash() {
                                const pending = this._save.pending;
                                for (let i = 1; i < pending.length; i++) {
                                    const step = pending[i];
                                    const previous = pending[i - 1];
                                    if (this._save.checkStepsCompatibility(step, previous)) {
                                        previous.after = step.after;
                                        previous.focusAfter = step.focusAfter;
                                        pending.splice(i, 1);
                                        i--
                                    }
                                }
                            }
                            checkStepsCompatibility(stepOne, stepTwo) {
                                return stepOne.fun_name == stepTwo.fun_name && stepOne.fun_name != && Object.values(stepOne.remove).toString() == Object.values(stepTwo.remove).toString() && Object.keys(stepOne.add).toString() == Object.keys(stepTwo.add).toString()
                            }
                            resetTmp() {
                                this._save.tmp = []
                            }
                            restore() {
                                const save = this._save;
                                if (save.pending.length > 0) {
                                    save.publish();
                                    save.debounce('clear')
                                }
                                if (save.versions.length == save.version) return;
                                let version = save.versions[save.version];
                                version.forEach(step => {
                                    save.content.remove(step.remove);
                                    save.content.add(step.add)
                                });
                                const focus = version[version.length - 1].focus;
                                this.lastX = focus.lastX;
                                this.pos.letter = focus.letter;
                                this.pos.line = focus.line;
                                this.pos.childIndex = focus.childIndex;
                                if (!this.is.line.visible(focus.line)) {
                                    this.render.move.page({
                                        offset: focus.line - Math.floor(this.render.linesLimit / 2)
                                    })
                                } else {
                                    this.render.move.page()
                                }
                                this.render.overflow.scrollTo(this.render.overflow.scrollLeft, this.render.hidden * this.settings.line);
                                save.version++
                            }
                            recall() {
                                const save = this._save;
                                if (save.version <= 0) return;
                                save.version--;
                                const version = save.versions[save.version];
                                version.reverse().forEach(step => {
                                    const keys = Object.keys(step.add);
                                    const min = Math.min(...keys);
                                    save.content.remove({
                                        sLine: min,
                                        len: Math.max(...keys) - min + 1
                                    });
                                    save.content.add(step.after)
                                });
                                version.reverse();
                                const focus = version[0].focusAfter;
                                if (!this.is.line.visible(focus.line)) this.render.move.page({
                                    offset: focus.line - Math.floor(this.render.linesLimit / 2)
                                });
                                else this.render.move.page();
                                this.render.overflow.scrollTo(this.render.overflow.scrollLeft, this.render.hidden * this.settings.line, );
                                this.caret.refocus(focus.letter, focus.line, focus.childIndex)
                            }
                        }
                        return {}
                    }
                    Ī.B = Ī => {
                        let styles;
                        return {
                            styles: [`.tabjf_editor-con {  overflow : auto;  background : #FFF;  color : #000;  }`, `.tabjf_editor-con .tabjf_editor {  position : relative;  min-height : calc( (var(--min-height, 0) - var(--paddingTop, 0)) * 1px);  padding-top : calc( var(--paddingTop, 0) * 1px ) ;  padding-right : 10px;  padding-top : calc( var(--paddingTop, 0) * 1px );  width : calc(var(--scroll-width, 100%) * 1px + 5px );  }`, `.tabjf_editor-con .tabjf_editor p {  position : relative;  line-height: 20px ;  min-height : 20px ;  max-height : 20px ;  height : 20px ;  cursor : text ;  display : flex ;  margin : 0 ;  padding : 0 ;  }`, `.tabjf_editor-con .tabjf_editor p::after {  display : block;  content : '█' ;  opacity : 0 ;  }`, `.tabjf_editor-con .tabjf_editor p span {  display : block ;  white-space : nowrap;  flex-shrink : 0 ;  }`, `.tabjf_editor-con .tabjf_editor p span:last-child {  margin-right: 10px;  }`, `@keyframes tabjf_blink {  0% { opacity: 1; }  50% { opacity: 0; }  100% { opacity: 1; }  }`, `.tabjf_editor-con .tabjf_editor .caret {  width : 1px ;  height : 20px;  position : absolute;  animation : tabjf_blink 1s linear infinite;  background-color : #000;  }`]
                        }
                    }
                    const Ī.a(Ī);
                    const Ī.b(Ī);
                    const Ī.c(Ī);
                    const Ī.d(Ī);
                    const Ī.e(Ī);
                    const Ī.f(Ī);
                    const Ī.g(Ī);
                    const Ī.h(Ī);
                    const Ī.i(Ī);
                    const Ī.j(Ī);
                    const Ī.k(Ī);
                    const Ī.l(Ī);
                    const Ī.m(Ī);
                    const Ī.n(Ī);
                    const Ī.o(Ī);
                    const Ī.p(Ī);
                    const Ī.r(Ī);
                    const Ī.s(Ī);
                    const Ī.t(Ī);
                    const Ī.u(Ī);
                    const Ī.w(Ī);
                    const Ī.x(Ī);
                    const Ī.q(Ī);
                    const Ī.v(Ī);
                    const Ī.µ(Ī);
                    const Ī.ê(Ī);
                    const Ī.õ(Ī);
                    const Ī.ú(Ī);
                    const Ī.A(Ī);
                    const {
                        styles
                    } = Ī.B(Ī);
                    Ī = undefined;
                    class TabJF {
                        editor;
                        lastX = 0;
                        clipboard = [];
                        copiedHere = false;
                        activated = false;
                        spaceUChar = '\u00A0';
                        updateMethod;
                        pressed = {
                            shift: false,
                            ctrl: false,
                            alt: false
                        };
                        pos = {
                            letter: null,
                            line: null,
                            el: null
                        };
                        selection = {
                            update: false,
                            reverse: false,
                            active: false,
                            expanded: false,
                            start: {
                                line: -1,
                                letter: -1,
                                node: -1
                            },
                            end: {
                                line: -1,
                                letter: -1,
                                node: -1
                            }
                        };
                        tabWidth = 2;
                        constructor(editor, set = {}) {
                            if (typeof editor?.nodeType == ) throw new Error('You can\'t create Editor JF without passing node to set as editor.');
                            if (editor.nodeType != 1) throw new Error('Editor node has to be of proper node type. [1]');
                            this.editor = editor;
                            this.editor.setAttribute('tabindex', '-1');
                            this.editor.classList.add('tabjf_editor');
                            const required = {
                                left: 0,
                                line: 20,
                                syntax: false,
                                contentText: false,
                                contentObj: false
                            };
                            Object.keys(required).forEach(attr => {
                                set[attr] = typeof set[attr]. == .
                                'undefined' ? required[attr]: set[attr]
                            });
                            this.settings = set;
                            this.settings.height = this.editor.offsetHeight;
                            this.inject();
                            this._save.debounce = this._hidden.debounce(this._save.publish, 500);
                            this.update.resizeDebounce = this._hidden.debounce(this.update.resize, 500);
                            const methodsSave = [
                                ['remove', 'selected'],
                                ['remove', 'one'],
                                ['remove', 'word'],
                                ['action', 'paste'],
                                ['newLine'],
                                ['mergeLine'],
                                ['insert']
                            ];
                            methodsSave.forEach(path => {
                                this.set.preciseMethodsProxy(this, path)
                            });
                            this.assignEvents();
                            this.caret.el = this.caret.create(this.editor);
                            this.caret.hide();
                            this.font.createLab();
                            this.render.init(this.settings.contentObj, this.settings.contentText);
                            this.action.createCopyGround();
                            if (this.settings.syntax) this.syntax.init();
                            this.truck.import(this.render.content, this.render.linesLimit);
                            this.addRules();
                            this.set.docEvents();
                            this.updateMethod = this.update.select.bind ? this.update.select.bind(this) : this.update.select
                        }
                        inject() {
                            const classes = [{
                                instance: TabJF_Hidden,
                                var: '_hidden'
                            }, {
                                instance: TabJF_Save,
                                var: '_save',
                                modules: [{
                                    instance: TabJF_Save_Set,
                                    var: 'set'
                                }, {
                                    instance: TabJF_Save_Content,
                                    var: 'content'
                                }]
                            }, {
                                instance: TabJF_Action,
                                var: 'action'
                            }, {
                                instance: TabJF_Caret,
                                var: 'caret'
                            }, {
                                instance: TabJF_Clear,
                                var: 'clear'
                            }, {
                                instance: TabJF_End,
                                var: 'end'
                            }, {
                                instance: TabJF_Event,
                                var: 'event'
                            }, {
                                instance: TabJF_Font,
                                var: 'font'
                            }, {
                                instance: TabJF_Get,
                                var: 'get'
                            }, {
                                instance: TabJF_Is,
                                var: 'is',
                                modules: [{
                                    instance: TabJF_Is_Line,
                                    var: 'line'
                                }]
                            }, {
                                instance: TabJF_Keys,
                                var: 'keys'
                            }, {
                                instance: TabJF_Remove,
                                var: 'remove'
                            }, {
                                instance: TabJF_Render,
                                var: 'render',
                                modules: [{
                                    instance: TabJF_Render_Fill,
                                    var: 'fill'
                                }, {
                                    instance: TabJF_Render_Move,
                                    var: 'move'
                                }, {
                                    instance: TabJF_Render_Add,
                                    var: 'add'
                                }, {
                                    instance: TabJF_Render_Remove,
                                    var: 'remove'
                                }, {
                                    instance: TabJF_Render_Set,
                                    var: 'set'
                                }, {
                                    instance: TabJF_Render_Update,
                                    var: 'update'
                                }]
                            }, {
                                instance: TabJF_Replace,
                                var: 'replace'
                            }, {
                                instance: TabJF_Set,
                                var: 'set'
                            }, {
                                instance: TabJF_Syntax,
                                var: 'syntax',
                                modules: [{
                                    instance: TabJF_Syntax_Create,
                                    var: 'create'
                                }]
                            }, {
                                instance: TabJF_Truck,
                                var: 'truck'
                            }, {
                                instance: TabJF_Update,
                                var: 'update',
                                modules: [{
                                    instance: TabJF_Update_Selection,
                                    var: 'selection'
                                }]
                            }];
                            classes.forEach(classObj => {
                                this.assignInjected(classObj)
                            })
                        }
                        assignInjected(classObj, context = this) {
                            const variable = classObj.var;
                            if (!context[variable]) {
                                context[variable] = {}
                            }
                            const classInstance = classObj.instance;
                            const getMethods = Object.getOwnPropertyNames(classInstance.prototype);
                            const instance = new classInstance.prototype.constructor;
                            if (!instance._name) {
                                instance._name = classInstance.name.replace(this.constructor.name + ).replaceAll('_', '.').toLowerCase()
                            }
                            const getProps = Object.getOwnPropertyNames(instance);
                            getMethods.forEach(name => {
                                if (name != 'constructor') {
                                    context[variable].[name] = classInstance.prototype[name].bind(this)
                                }
                            });
                            getProps.forEach(name => {
                                context[variable].[name] = instance[name]
                            });
                            if (classObj?.modules) {
                                classObj.modules.forEach(moduleObj => {
                                    this.assignInjected(moduleObj, this[variable])
                                })
                            }
                        }
                        addRules() {
                            if (TabJF.prototype.cssAdded) return;
                            var styleEl = document.createElement('style');
                            styleEl.setAttribute('name', "TabJF Styles");
                            document.head.insertBefore(styleEl, document.head.children[0]);
                            const css = styleEl.sheet;
                            styles.forEach(rule => {
                                css.insertRule(rule, css.cssRules.length)
                            });
                            TabJF.prototype.cssAdded = true
                        }
                        _proxySaveHandle = {
                            main: this,
                            apply: function(target, scope, args) {
                                const main = this.main;
                                const save = main._save;
                                const name = target.name.replace('bound ', '');
                                save.debounce();
                                const oldInProggress = save.inProgress;
                                save.inProgress = true;
                                const step = save.tmp.length;
                                save.methodsStack.push(name);
                                let startLine = main.pos.line;
                                const sel = main.get.selection();
                                if (sel.type.toLowerCase() == ) {
                                    startLine = main.selection.start.line;
                                    if (main.selection.start.line > main.selection.end.line) {
                                        startLine = main.selection.end.line
                                    }
                                }
                                save.set.add(name, args);
                                const results = target.bind(main)(...args);
                                save.set.remove(name, args, step, startLine);
                                if (!oldInProggress) {
                                    save.methodsStack = [];
                                    save.inProgress = false;
                                    save.moveToPending()
                                }
                                return results
                            }
                        };
                        assignEvents() {
                            this.editor.addEventListener("mousedown", this.active.bind ? this.active.bind(this) : this.active);
                            this.editor.addEventListener("mouseup", this.stopSelect.bind ? this.stopSelect.bind(this) : this.stopSelect);
                            this.editor.addEventListener("focusout", this.deactive.bind ? this.deactive.bind(this) : this.deactive);
                            this.editor.addEventListener("dblclick", this.saveSelectionDblClick.bind ? this.saveSelectionDblClick.bind(this) : this.saveSelectionDblClick)
                        }
                        saveSelectionDblClick(e) {
                            this.update.select();
                            this.update.selection.start(0, this.selection.end.line, this.selection.end.node);
                            this.checkSelect()
                        }
                        stopSelect(e) {
                            this.editor.removeEventListener('mousemove', this.updateMethod, true);
                            if (this.get.selection().type == ) {
                                const event = this.event.dispatch('tabJFSelectStop', {
                                    pos: this.get.clonedPos(),
                                    event: e,
                                    selection: this.get.clone(this.selection)
                                });
                                if (event.defaultPrevented) return
                            }
                            this.selection.update = false;
                            this.checkSelect()
                        }
                        checkSelect() {
                            if (!this.selection.active) return;
                            const start = this.selection.start;
                            const end = this.selection.end;
                            let reversed = false;
                            if (start.line < this.render.hidden && end.line < this.render.hidden) return;
                            let lineEndPos = end.line;
                            let lineEndChildIndex = end.node;
                            let lineStartChildIndex = start.node;
                            let firstLinePos, startLetter, endLetter;
                            if (lineEndPos < start.line || lineEndPos == start.line && lineEndChildIndex < lineStartChildIndex || lineEndPos == start.line && lineEndChildIndex == lineStartChildIndex && end.letter < start.letter) {
                                reversed = true;
                                startLetter = end.letter;
                                endLetter = start.letter;
                                firstLinePos = lineEndPos;
                                lineEndPos = start.line;
                                const tmp = lineStartChildIndex;
                                lineStartChildIndex = lineEndChildIndex;
                                lineEndChildIndex = tmp
                            } else {
                                startLetter = start.letter;
                                endLetter = end.letter;
                                firstLinePos = start.line
                            }
                            if (firstLinePos < this.render.hidden || (this.selection.update && firstLinePos >= this.render.hidden + this.render.linesLimit)) {
                                firstLinePos = this.render.hidden;
                                startLetter = 0;
                                lineStartChildIndex = 0;
                                endLetter = end.letter
                            }
                            if (endLetter < 0) {
                                return
                            }
                            if (lineEndPos >= this.render.hidden + this.render.linesLimit) {
                                lineEndPos = this.render.hidden + this.render.linesLimit - 1;
                                let endLine = this.get.lineByPos(lineEndPos);
                                let endChild = endLine.children[endLine.children.length - 1];
                                lineEndChildIndex = endChild.childNodes.length - 1;
                                endLetter = endChild.childNodes[endChild.childNodes.length - 1].nodeValue.length
                            }
                            let firstText = this.get.lineByPos(firstLinePos);
                            let lastText = this.get.lineByPos(lineEndPos);
                            if (!firstText || !lastText) {
                                return
                            }
                            firstText = firstText.children[lineStartChildIndex].childNodes[0];
                            lastText = lastText.children[lineEndChildIndex].childNodes[0];
                            const range = new Range;
                            const firstTextLength = firstText.nodeValue.length;
                            const lastTextLength = lastText.nodeValue.length;
                            if (firstTextLength < startLetter) startLetter = firstTextLength;
                            if (lastTextLength < endLetter) endLetter = lastTextLength;
                            range.setStart(firstText, startLetter);
                            range.setEnd(lastText, endLetter);
                            this.get.selection().removeAllRanges();
                            this.get.selection().addRange(range)
                        }
                        active(e) {
                            const event = this.event.dispatch('tabJFActivate', {
                                pos: this.get.clonedPos(),
                                event: e
                            });
                            if (event.defaultPrevented) return;
                            if (e.target == this.editor || e.x < 0 || e.y < 0) return;
                            let el = e.target;
                            if (el.nodeName === ) el = el.children[el.children.length - 1];
                            const line = el.parentElement.offsetTop / this.settings.line;
                            const letter = this.font.getLetterByWidth(el.innerText, el, e.clientX - el.offsetLeft - this.settings.left);
                            this.caret.show();
                            const index = this.get.childIndex(el);
                            this.caret.refocus(letter, line, index, );
                            if (line < this.render.hidden + 2 && this.render.hidden > 0) {
                                this.render.set.overflow(null, (line - 2) * this.settings.line)
                            } else if (line > this.render.hidden + this.render.linesLimit - 5) {
                                this.render.set.overflow(null, (line - (this.render.linesLimit - 5)) * this.settings.line)
                            }
                            this.lastX = this.get.realPos().x;
                            this.selection.start = {
                                line: line,
                                letter,
                                node: index
                            };
                            this.selection.end = {
                                line: -1,
                                letter: -1,
                                node: -1
                            };
                            this.selection.active = false;
                            this.editor.addEventListener('mousemove', this.updateMethod, true);
                            this.activated = true;
                            this.resetPressed()
                        }
                        resetPressed() {
                            this.pressed.ctrl = false;
                            this.pressed.shift = false;
                            this.pressed.alt = false
                        }
                        deactive(e) {
                            const event = this.event.dispatch('tabJFDeactivate', {
                                pos: this.get.clonedPos(),
                                event: e
                            });
                            if (event.defaultPrevented) return;
                            this.caret.hide();
                            this.copiedHere = false;
                            this.activated = false
                        }
                        key(e) {
                            if (!this.activated) return;
                            const type = e.type;
                            if (type == 'keydown') {
                                const event = this.event.dispatch('tabJFKeyDown', {
                                    pos: this.get.clonedPos(),
                                    event: e
                                });
                                if (event.defaultPrevented) return
                            } else if (type == 'keyup') {
                                const event = this.event.dispatch('tabJFKeyUp', {
                                    pos: this.get.clonedPos(),
                                    event: e
                                });
                                if (event.defaultPrevented) return
                            }
                            this.update.specialKeys(e);
                            if (type == 'keyup') return;
                            const prevent = {
                                33: true,
                                34: true,
                                35: true,
                                36: true,
                                37: true,
                                38: true,
                                39: true,
                                40: true,
                                222: true
                            };
                            const skip = {
                                112: true,
                                113: true,
                                114: true,
                                115: true,
                                116: true,
                                117: true,
                                118: true,
                                119: true,
                                120: true,
                                121: true,
                                122: true,
                                123: true
                            };
                            if (skip[e.keyCode]) return;
                            if (prevent[e.keyCode]) e.preventDefault();
                            const keys = {
                                0: (e, type) => {},
                                8: (e, type) => {
                                    this.keys.backspace(e)
                                },
                                9: (e, type) => {
                                    this.keys.tab(e)
                                },
                                13: (e, type) => {
                                    this.keys.enter(e)
                                },
                                16: (e, type) => {
                                    const selection = this.get.selection();
                                    if (selection.type == ) {
                                        this.update.selection.start()
                                    }
                                },
                                17: (e, type) => {},
                                18: (e, type) => {},
                                20: (e, type) => {},
                                27: (e, type) => {
                                    this.keys.escape(e)
                                },
                                32: (e, type) => {
                                    e.preventDefault();
                                    this.keys.space(e)
                                },
                                33: (e, type) => {
                                    this.toSide(-1, -1)
                                },
                                34: (e, type) => {
                                    this.toSide(1, 1)
                                },
                                35: (e, type) => {
                                    this.toSide(1, 0)
                                },
                                36: (e, type) => {
                                    this.toSide(-1, 0)
                                },
                                46: (e, type) => {
                                    this.keys.delete(e)
                                },
                                37: (e, type) => {
                                    const event = this.event.dispatch('tabJFMove', {
                                        pos: this.get.clonedPos(),
                                        event: e,
                                        selection: this.get.clone(this.selection),
                                        x: -1,
                                        y: 0
                                    });
                                    if (event.defaultPrevented) return;
                                    this.keys.move(-1, 0)
                                },
                                38: (e, type) => {
                                    const event = this.event.dispatch('tabJFMove', {
                                        pos: this.get.clonedPos(),
                                        event: e,
                                        selection: this.get.clone(this.selection),
                                        x: 0,
                                        y: -1
                                    });
                                    if (event.defaultPrevented) return;
                                    this.keys.move(0, -1)
                                },
                                39: (e, type) => {
                                    const event = this.event.dispatch('tabJFMove', {
                                        pos: this.get.clonedPos(),
                                        event: e,
                                        selection: this.get.clone(this.selection),
                                        x: 1,
                                        y: 0
                                    });
                                    if (event.defaultPrevented) return;
                                    this.keys.move(1, 0)
                                },
                                40: (e, type) => {
                                    const event = this.event.dispatch('tabJFMove', {
                                        pos: this.get.clonedPos(),
                                        event: e,
                                        selection: this.get.clone(this.selection),
                                        x: 0,
                                        y: 1
                                    });
                                    if (event.defaultPrevented) return;
                                    this.keys.move(0, 1)
                                },
                                45: (e, type) => {},
                                65: (e, type) => {
                                    if (this.pressed.ctrl) {
                                        e.preventDefault();
                                        this.action.selectAll()
                                    } else {
                                        this.insert(e.key)
                                    }
                                },
                                67: (e, type) => {
                                    if (this.pressed.ctrl) {
                                        this.action.copy()
                                    } else {
                                        this.insert(e.key)
                                    }
                                },
                                86: (e, type) => {
                                    if (!this.pressed.ctrl) {
                                        this.insert(e.key)
                                    }
                                },
                                88: (e, type) => {
                                    if (this.pressed.ctrl) {
                                        this.action.cut()
                                    } else {
                                        this.insert(e.key)
                                    }
                                },
                                89: (e, type) => {
                                    if (this.pressed.ctrl) {
                                        this.action.redo()
                                    } else {
                                        this.insert(e.key)
                                    }
                                },
                                90: (e, type) => {
                                    if (this.pressed.ctrl) {
                                        this.action.undo()
                                    } else {
                                        this.insert(e.key)
                                    }
                                },
                                91: (e, type) => {},
                                106: (e, type) => {
                                    this.insert('*')
                                },
                                109: (e, type) => {
                                    this.insert('-')
                                },
                                111: (e, type) => {
                                    e.preventDefault();
                                    this.insert('/')
                                },
                                144: (e, type) => {},
                                145: (e, type) => {},
                                182: (e, type) => {},
                                183: (e, type) => {},
                                191: (e, type) => {
                                    e.preventDefault();
                                    if (this.pressed.shift) {
                                        this.insert('?')
                                    } else {
                                        this.insert('/')
                                    }
                                },
                                192: (e, type) => {
                                    if (this.pressed.shift) this.insert('~');
                                    else this.insert('`')
                                },
                                default: (e, type) => {
                                    throw new Error('Unknow special key', e.keyCode)
                                }
                            };
                            const selDelSkip = {
                                delete: true,
                                backspace: true,
                                escape: true
                            };
                            const sel = this.get.selection();
                            const replaceKey = {
                                192: key => {
                                    return this.pressed.shift ?
                                },
                                default: key => key
                            };
                            const key = (replaceKey[e.keyCode] || replaceKey['default'])(e.key);
                            if (this.selection.active && !selDelSkip[key.toLowerCase()] && !this.pressed.ctrl && sel.type == && (!!this.keys[key.toLowerCase()] || key.length == 1)) {
                                this.remove.selected()
                            }
                            if (!keys[e.keyCode] && key.length == 1) {
                                this.insert(key)
                            } else {
                                (keys[e.keyCode] || keys['default'])(e, type)
                            }
                            const preventScroll = {
                                16: true,
                                17: true,
                                18: true,
                                20: true
                            };
                            if (!preventScroll[e.keyCode] && !this.caret.isVisible()) {
                                this.render.set.overflow(null, (this.pos.line - (this.render.linesLimit / 2)) * this.settings.line)
                            }
                            const skipUpdate = {
                                86: () => {
                                    if (!this.pressed.ctrl) return false;
                                    return true
                                },
                                67: () => true,
                                default: () => false
                            };
                            if (!(skipUpdate[e.keyCode] || skipUpdate['default'])()) {
                                this.update.page();
                                this.render.update.scrollWidthWithCurrentLine();
                                if (!preventScroll[e.keyCode]) {
                                    this.caret.scrollToX()
                                }
                            }
                        }
                        toSide(dirX, dirY) {
                            let line = this.pos.line;
                            let letter = this.pos.letter;
                            let node = this.pos.childIndex;
                            if (dirY > 0) {
                                line = this.render.content.length - 1
                            } else if (dirY < 0) {
                                line = 0
                            }
                            if (dirX > 0) {
                                let lineContent = this.render.content[line];
                                node = lineContent.content.length - 1;
                                let lastSpan = lineContent.content[lineContent.content.length - 1];
                                letter = this.replace.spaceChars(lastSpan.content).length
                            } else if (dirX < 0) {
                                letter = 0;
                                node = 0
                            }
                            const chosenLine = this.render.content[line];
                            if (chosenLine.content.length - 1 < node) {
                                node = chosenLine.content.length - 1
                            }
                            if (this.replace.spaceChars(chosenLine.content[node].content).length < letter) {
                                letter = this.replace.spaceChars(chosenLine.content[node].content).length
                            }
                            this.caret.refocus(letter, line, node);
                            this.lastX = this.get.realPos().x
                        }
                        newLine() {
                            let el = this.pos.el,
                                text = this.get.splitRow();
                            if (text.pre.innerText.length > 0) {
                                el.parentElement.insertBefore(text.pre, el);
                                el.remove();
                                el = text.pre
                            } else {
                                el.innerHTML = '';
                                el.appendChild(document.createTextNode(''))
                            }
                            this.render.content[this.pos.line].content = this.truck.exportLine(el.parentElement).content;
                            let newLine = document.createElement("p");
                            let appended = [];
                            text.suf.forEach(span => {
                                if (span.innerText.length > 0) {
                                    newLine.appendChild(span);
                                    appended.push(span)
                                }
                            });
                            if (appended.length == 0) {
                                text.suf[0].appendChild(document.createTextNode(''));
                                newLine.appendChild(text.suf[0]);
                                appended.push(text.suf[0])
                            }
                            this.render.content.splice(this.pos.line + 1, 0, this.truck.exportLine(newLine));
                            if (this.pos.line + 1 > this.render.hidden + this.render.linesLimit - 6) {
                                this.render.set.overflow(null, (this.pos.line - (this.render.linesLimit - 6)) * this.settings.line);
                                this.render.move.page({
                                    offset: this.pos.line - (this.render.linesLimit - 6)
                                })
                            } else {
                                this.render.move.page()
                            }
                            this.caret.refocus(0, this.pos.line + 1, 0);
                            this.lastX = 0;
                            this.render.update.minHeight();
                            this.render.update.scrollWidth()
                        }
                        mergeLine(dir) {
                            let line = this.get.line(this.pos.el);
                            if (line.nodeName != ) throw new Error("Parent has wrong tag, can't merge lines");
                            if (dir < 0) {
                                this.pos.line--;
                                this.toSide(1, 0);
                                this.render.content[this.pos.line].content = this.render.content[this.pos.line].content.concat(this.render.content[this.pos.line + 1].content);
                                this.render.content.splice(this.pos.line + 1, 1);
                                this.lastX = this.get.realPos().x
                            } else if (dir > 0) {
                                this.render.content[this.pos.line].content = this.render.content[this.pos.line].content.concat(this.render.content[this.pos.line + 1].content);
                                this.render.content.splice(this.pos.line + 1, 1)
                            }
                            this.render.update.minHeight();
                            this.render.update.scrollWidth()
                        }
                        insert(key) {
                            let text = this.replace.spaceChars(this.render.content[this.pos.line].content[this.pos.childIndex].content);
                            text = {
                                pre: text.substr(0, this.pos.letter),
                                suf: text.substr(this.pos.letter)
                            };
                            text = this.replace.spaces(text.pre) + key + this.replace.spaces(text.suf);
                            this.render.content[this.pos.line].content[this.pos.childIndex].content = text;
                            this.caret.refocus(this.pos.letter + this.replace.spaceChars(key).length);
                            this.lastX = this.get.realPos().x
                        }
                        catchClipboard(e) {
                            if (!this.activated) {
                                return
                            }
                            if (!this.copiedHere) {
                                let paste = (event.clipboardData || window.clipboardData).getData('text');
                                this.clipboard = this.truck.exportText(paste)
                            }
                            this.action.paste()
                        }
                    }
                    export {
                        TabJF
                    };
