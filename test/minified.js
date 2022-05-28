class a {
    J b = __f;
    c = [];
    d = false;
    e = false;
    f = '\u00A0';
    V g = {
        ê: false,
        õ: false,
        ú: false
    };
    h = {
        A: null,
        B: null,
        C: null
    };
    i = {
        D: false,
        E: false,
        t: false,
        F: false,
        G: {
            B: -1,
            A: -1,
            I: -1
        },
        H: {
            B: -1,
            A: -1,
            I: -1
        }
    };
    j = 2;
    constructor(J, K = {}) {
        if (typeof J ? .nodeType == 'undefined') throw new Error('You can\'t create Editor JF without passing node to set as editor.');
        if (J.nodeType != 1) throw new Error('Editor node has to be of proper node type. [1]');
        this.J = J;
        this.J.setAttribute('tabindex', '-1');
        this.J.classList.add('tabjf_editor');
        const L = {
            M: __f,
            B: __m,
            N: false,
            O: false,
            P: false
        };
        Object.__e(L).forEach(attr => {
            K[attr] = typeof K[attr] == 'undefined' ? L[attr] : K[attr]
        });
        this.Q = K;
        this.Q.R = this.J.offsetHeight;
        this.k();
        this._save.S = this._hidden.S(this._save.publish, 500);
        this.D.T = this._hidden.S(this.D.resize, 500);
        const U = [
            ['remove', 'selected'],
            ['remove', 'one'],
            ['remove', 'word'],
            ['action', 'paste'],
            ['newLine'],
            ['mergeLine'],
            ['insert']
        ];
        U.forEach(path => {
            this.K.preciseMethodsProxy(this, path)
        });
        this.o();
        this.caret.C = this.caret.create(this.J);
        this.caret.hide();
        this.font.createLab();
        this.render.init(this.Q.P, this);
        this.action.createCopyGround();
        if (this.Q.N) this.N.init();
        this.truck.import(this.render.__S, this);
        this.m();
        this.K.docEvents();
        this.V = this.D.select.bind ? this.D.select.bind(this) : this.D.select
    }
    k() {
        const W = [{
            X: TabJF_Hidden,
            Y: '_hidden'
        }, {
            X: TabJF_Save,
            Y: '_save',
            Z: [{
                X: TabJF_Save_Set,
                Y: 'set'
            }, {
                X: TabJF_Save_Content,
                Y: 'content'
            }]
        }, {
            X: TabJF_Action,
            Y: 'action'
        }, {
            X: TabJF_Caret,
            Y: 'caret'
        }, {
            X: TabJF_Clear,
            Y: 'clear'
        }, {
            X: TabJF_End,
            Y: 'end'
        }, {
            X: TabJF_Event,
            Y: 'event'
        }, {
            X: TabJF_Font,
            Y: 'font'
        }, {
            X: TabJF_Get,
            Y: 'get'
        }, {
            X: TabJF_Is,
            Y: 'is',
            Z: [{
                X: TabJF_Is_Line,
                Y: 'line'
            }]
        }, {
            X: TabJF_Keys,
            Y: 'keys'
        }, {
            X: TabJF_Remove,
            Y: 'remove'
        }, {
            X: TabJF_Render,
            Y: 'render',
            Z: [{
                X: TabJF_Render_Fill,
                Y: 'fill'
            }, {
                X: TabJF_Render_Move,
                Y: 'move'
            }, {
                X: TabJF_Render_Add,
                Y: 'add'
            }, {
                X: TabJF_Render_Remove,
                Y: 'remove'
            }, {
                X: TabJF_Render_Set,
                Y: 'set'
            }, {
                X: TabJF_Render_Update,
                Y: 'update'
            }]
        }, {
            X: TabJF_Replace,
            Y: 'replace'
        }, {
            X: TabJF_Set,
            Y: 'set'
        }, {
            X: TabJF_Syntax,
            Y: 'syntax',
            Z: [{
                X: TabJF_Syntax_Create,
                Y: 'create'
            }]
        }, {
            X: TabJF_Truck,
            Y: 'truck'
        }, {
            X: TabJF_Update,
            Y: 'update',
            Z: [{
                X: TabJF_Update_Selection,
                Y: 'selection'
            }]
        }];
        W.forEach(Ī => {
            this.l(Ī)
        })
    }
    l(Ī, $ = this) {
        const _ = Ī.Y;
        if (!$[_]) {
            $[_] = {}
        }
        const _a = Ī.X;
        const _b = Object.getOwnPropertyNames(_a.prototype);
        const X = new classInstance.prototype.constructor;
        if (!X._c) {
            X._c = _a._n.replace(this.constructor._n + '_', '').replaceAll('_', '.').toLowerCase()
        }
        const _d = Object.getOwnPropertyNames(X);
        _b.forEach(_n => {
            if (_n != 'constructor') {
                $[_];
                [_n];
                context[variable][name] = _a.prototype[_n].bind(this)
            }
        });
        _d.forEach(_n => {
            $[_];
            [_n];
            context[variable][name] = X[_n]
        });
        if (Ī ? .Z) {
            Ī.Z.forEach(moduleObj => {
                this.l(moduleObj, this[_])
            })
        }
    }
    m() {
        if (a.prototype._g) return;
        var _e = document.createElement('style');
        _e.setAttribute('name', "TabJF Styles");
        document.head.insertBefore(_e, document.head.children[__f]);
        const _f = _e.sheet;
        styles.forEach(rule => {
            _f.insertRule(rule, _f.cssRules.length)
        });
        a.prototype._g = true
    }
    n = {
        _h: this,
        _i: function(_j, _k, _l) {
            const _h = this._h;
            const _m = _h._save;
            const _n = _j._n.replace('bound ', '');
            _m.S();
            const _o = _m._p;
            _m._p = true;
            const _r = _m._C.length;
            _m._w.push(_n);
            let _s = _h.h.B;
            const _t = _h.get.i();
            if (_t._J.toLowerCase() == 'range') {
                _s = _h.i.G.B;
                if (_h.i.G.B > _h.i.H.B) {
                    _s = _h.i.H.B
                }
            }
            _m.K.add(_n, _l);
            const _u = _j.bind(_h)(..._l);
            _m.K.remove(_n, _l, _r, _s);
            if (!_o) {
                _m._w = [];
                _m._p = false;
                _m.moveToPending()
            }
            return _u
        }
    };
    o() {
        this.J.addEventListener("mousedown", this.t.bind ? this.t.bind(this) : this.t);
        this.J.addEventListener("mouseup", this.r.bind ? this.r.bind(this) : this.r);
        this.J.addEventListener("focusout", this.w.bind ? this.w.bind(this) : this.w);
        this.J.addEventListener("dblclick", this.p.bind ? this.p.bind(this) : this.p)
    }
    p(_z) {
        this.D.select();
        this.D.i.G(__f, this.i.H.B, this.i.H.I);
        this.s()
    }
    r(_z) {
        this.J.removeEventListener('mousemove', this.V, true);
        if (this.get.i()._J == 'Range') {
            const _y = this._y.dispatch('tabJFSelectStop', {
                h: this.get.clonedPos(),
                _y: _z,
                i: this.get.clone(this.i)
            });
            if (_y.defaultPrevented) return
        }
        this.i.D = false;
        this.s()
    }
    s() {
        if (!this.i.t) return;
        const G = this.i.G;
        const H = this.i.H;
        let _v = false;
        if (G.B < this.render.hidden && H.B < this.render.hidden) return;
        let _µ = H.B;
        let _ê = H.I;
        let _õ = G.I;
        let _ú, _A, _B;
        if (_µ < G.B || _µ == G.B && _ê < _õ || _µ == G.B && _ê == _õ && H.A < G.A) {
            _v = true;
            _A = H.A;
            _B = G.A;
            _ú = _µ;
            _µ = G.B;
            const _C = _õ;
            _õ = _ê;
            _ê = _C
        } else {
            _A = G.A;
            _B = H.A;
            _ú = G.B
        }
        if (_ú < this.render.hidden || (this.i.D && _ú >= this.render.hidden + this.render.linesLimit)) {
            _ú = this.render.hidden;
            _A = __f;
            _õ = __f;
            _B = H.A
        }
        if (_B < __f) {
            return
        }
        if (_µ >= this.render.hidden + this.render.linesLimit) {
            _µ = this.render.hidden + this.render.linesLimit - 1;
            let _D = this.get.lineByPos(_µ);
            let _E = _D.children[_D.children.length - 1];
            _ê = _E.childNodes.length - 1;
            _B = _E.childNodes[_E.childNodes.length - 1].nodeValue.length
        }
        let _x = this.get.lineByPos(_ú);
        let _q = this.get.lineByPos(_µ);
        if (!_x || !_q) {
            return
        }
        _x = _x.children[_õ].childNodes[__f];
        _q = _q.children[_ê].childNodes[__f];
        const _F = new Range;
        const _G = _x.nodeValue.length;
        const _H = _q.nodeValue.length;
        if (_G < _A) _A = _G;
        if (_H < _B) _B = _H;
        _F.setStart(_x, _A);
        _F.setEnd(_q, _B);
        this.get.i().removeAllRanges();
        this.get.i().addRange(_F)
    }
    t(_z) {
        const _y = this._y.dispatch('tabJFActivate', {
            h: this.get.clonedPos(),
            _y: _z
        });
        if (_y.defaultPrevented) return;
        if (_z._j == this.J || _z.x < __f || _z.y < __f) return;
        let C = _z._j;
        if (C.nodeName === "P") C = C.children[C.children.length - 1];
        const B = C.parentElement.offsetTop / this.Q.B;
        const A = this.font.getLetterByWidth(C.innerText, C, _z - C.offsetLeft - this.Q.M);
        this.caret.show();
        const _I = this.get.childIndex(C);
        this.caret.refocus(A, B, _I, );
        if (B < this.render.hidden + 2 && this.render.hidden > __f) {
            this.render.K.overflow(null, (B - 2) * this.Q.B)
        } else if (B > this.render.hidden + this.render.linesLimit - 5) {
            this.render.K.overflow(null, (B - (this.render.linesLimit - 5)) * this.Q.B)
        }
        this.b = this.get.realPos().x;
        this.i.G = {
            B: B,
            A,
            I: _I
        };
        this.i.H = {
            B: -1,
            A: -1,
            I: -1
        };
        this.i.t = false;
        this.J.addEventListener('mousemove', this.V, true);
        this.e = true;
        this.u()
    }
    u() {
        this.g.õ = false;
        this.g.ê = false;
        this.g.ú = false
    }
    w(_z) {
        const _y = this._y.dispatch('tabJFDeactivate', {
            h: this.get.clonedPos(),
            _y: _z
        });
        if (_y.defaultPrevented) return;
        this.caret.hide();
        this.d = false;
        this.e = false
    }
    z(_z) {
        if (!this._z) return;
        const _J = _z._J;
        if (_J == 'keydown') {
            const _y = this._y.dispatch('tabJFKeyDown', {
                h: this.get.clonedPos(),
                _y: _z
            });
            if (_y.defaultPrevented) return
        } else if (_J == 'keyup') {
            const _y = this._y.dispatch('tabJFKeyUp', {
                h: this.get.clonedPos(),
                _y: _z
            });
            if (_y.defaultPrevented) return
        }
        this.D.specialKeys(_z);
        if (_J == 'keyup') return;
        const _K = {
            _L: true,
            _M: true,
            _N: true,
            _O: true,
            _P: true,
            _Q: true,
            _R: true,
            _S: true,
            _T: true
        };
        const _U = {
            _V: true,
            _W: true,
            _X: true,
            _Y: true,
            _Z: true,
            _Ī: true,
            _$: true,
            __: true,
            __a: true,
            __b: true,
            __c: true,
            __d: true
        };
        if (_U[_z.keyCode]) return;
        if (_K[_z.keyCode]) _z.preventDefault();
        const __e = {
            __f: (_z, _J) => {},
            __g: (_z, _J) => {
                this.__e.__G(_z)
            },
            __h: (_z, _J) => {
                this.__e.tab(_z)
            },
            __i: (_z, _J) => {
                this.__e.enter(_z)
            },
            __j: (_z, _J) => {
                const i = this.get.i();
                if (i._J == 'Caret') {
                    this.D.i.G()
                }
            },
            __k: (_z, _J) => {},
            __l: (_z, _J) => {},
            __m: (_z, _J) => {},
            __n: (_z, _J) => {
                this.__e.__H(_z)
            },
            __o: (_z, _J) => {
                _z.preventDefault();
                this.__e.space(_z)
            },
            _L: (_z, _J) => {
                this.y(-1, -1)
            },
            _M: (_z, _J) => {
                this.y(1, 1)
            },
            _N: (_z, _J) => {
                this.y(1, __f)
            },
            _O: (_z, _J) => {
                this.y(-1, __f)
            },
            __p: (_z, _J) => {
                this.__e.__F(_z)
            },
            _P: (_z, _J) => {
                const _y = this._y.dispatch('tabJFMove', {
                    h: this.get.clonedPos(),
                    _y: _z,
                    i: this.get.clone(this.i),
                    x: -1,
                    y: __f
                });
                if (_y.defaultPrevented) return;
                this.__e.move(-1, __f)
            },
            _Q: (_z, _J) => {
                const _y = this._y.dispatch('tabJFMove', {
                    h: this.get.clonedPos(),
                    _y: _z,
                    i: this.get.clone(this.i),
                    x: __f,
                    y: -1
                });
                if (_y.defaultPrevented) return;
                this.__e.move(__f, -1)
            },
            _R: (_z, _J) => {
                const _y = this._y.dispatch('tabJFMove', {
                    h: this.get.clonedPos(),
                    _y: _z,
                    i: this.get.clone(this.i),
                    x: 1,
                    y: __f
                });
                if (_y.defaultPrevented) return;
                this.__e.move(1, __f)
            },
            _S: (_z, _J) => {
                const _y = this._y.dispatch('tabJFMove', {
                    h: this.get.clonedPos(),
                    _y: _z,
                    i: this.get.clone(this.i),
                    x: __f,
                    y: 1
                });
                if (_y.defaultPrevented) return;
                this.__e.move(__f, 1)
            },
            __r: (_z, _J) => {},
            __s: (_z, _J) => {
                if (this.g.õ) {
                    _z.preventDefault();
                    this.action.selectAll()
                } else {
                    this.v(_z.z)
                }
            },
            __t: (_z, _J) => {
                if (this.g.õ) {
                    this.action.copy()
                } else {
                    this.v(_z.z)
                }
            },
            __u: (_z, _J) => {
                if (!this.g.õ) {
                    this.v(_z.z)
                }
            },
            __w: (_z, _J) => {
                if (this.g.õ) {
                    this.action.cut()
                } else {
                    this.v(_z.z)
                }
            },
            __z: (_z, _J) => {
                if (this.g.õ) {
                    this.action.redo()
                } else {
                    this.v(_z.z)
                }
            },
            __y: (_z, _J) => {
                if (this.g.õ) {
                    this.action.undo()
                } else {
                    this.v(_z.z)
                }
            },
            __x: (_z, _J) => {},
            __q: (_z, _J) => {
                this.v('*')
            },
            __v: (_z, _J) => {
                this.v('-')
            },
            __µ: (_z, _J) => {
                _z.preventDefault();
                this.v('/')
            },
            __ê: (_z, _J) => {},
            __õ: (_z, _J) => {},
            __ú: (_z, _J) => {},
            __A: (_z, _J) => {},
            __B: (_z, _J) => {
                _z.preventDefault();
                if (this.g.ê) {
                    this.v('?')
                } else {
                    this.v('/')
                }
            },
            __C: (_z, _J) => {
                if (this.g.ê) this.v('~');
                else this.v('`')
            },
            __D: (_z, _J) => {
                throw new Error('Unknow special key', _z.keyCode)
            }
        };
        const __E = {
            __F: true,
            __G: true,
            __H: true
        };
        const _t = this.get.i();
        const __I = {
            __C: z => {
                return this.g.ê ? '~' : '`'
            },
            __D: z => z
        };
        const z = (__I[_z.keyCode] || __I['__D'])(_z.z);
        if (this.i.t && !__E[z.toLowerCase()] && !this.g.õ && _t._J == "Range" && (!!this.__e[z.toLowerCase()] || z.length == 1)) {
            this.remove.selected()
        }
        if (!__e[_z.keyCode] && z.length == 1) {
            this.v(z)
        } else {
            (__e[_z.keyCode] || __e['__D']);
            (_z, _J)
        }
        const __J = {
            __j: true,
            __k: true,
            __l: true,
            __m: true
        };
        if (!__J[_z.keyCode] && !this.caret.isVisible()) {
            this.render.K.overflow(null, (this.h.B - (this.render.linesLimit / 2)) * this.Q.B)
        }
        const __K = {
            __u: () => {
                if (!this.g.õ) return false;
                return true
            },
            __t: () => true,
            __D: () => false
        };
        if (!(__K[_z.keyCode] || __K['__D'])()) {
            this.D.page();
            this.render.D.scrollWidthWithCurrentLine();
            if (!__J[_z.keyCode]) {
                this.caret.scrollToX()
            }
        }
    }
    y(__L, __M) {
        let B = this.h.B;
        let A = this.h.A;
        let I = this.h.childIndex;
        if (__M > __f) {
            B = this.render.__S.length - 1
        } else if (__M < __f) {
            B = __f
        }
        if (__L > __f) {
            let __N = this.render.__S[B];
            I = __N.__S.length - 1;
            let __O = __N.__S[__N.__S.length - 1];
            A = this.replace.spaceChars(__O.__S).length
        } else if (__L < __f) {
            A = __f;
            I = __f
        }
        const __P = this.render.__S[B];
        if (__P.__S.length - 1 < I) {
            I = __P.__S.length - 1
        }
        if (this.replace.spaceChars(__P.__S[I].__S).length < A) {
            A = this.replace.spaceChars(__P.__S[I].__S).length
        }
        this.caret.refocus(A, B, I);
        this.b = this.get.realPos().x
    }
    x() {
        let C = this.h.C,
            __Q = this.get.splitRow();
        if (__Q.__V.innerText.length > __f) {
            C.parentElement.insertBefore(__Q.__V, C);
            C.remove();
            C = __Q.__V
        } else {
            C.__R = '';
            C.appendChild(document.createTextNode(''))
        }
        this.render.__S[this.h.B].__S = this.truck.exportLine(C.parentElement).__S;
        let x = document.createElement("p");
        let __T = [];
        __Q.suf.forEach(span => {
            if (span.innerText.length > __f) {
                x.appendChild(span);
                __T.push(span)
            }
        });
        if (__T.length == __f) {
            __Q.suf[__f].appendChild(document.createTextNode(''));
            x.appendChild(__Q.suf[__f]);
            __T.push(__Q.suf[__f])
        }
        this.render.__S.splice(this.h.B + 1, __f, this.truck.exportLine(x));
        if (this.h.B + 1 > this.render.hidden + this.render.linesLimit - 6) {
            this.render.K.overflow(null, (this.h.B - (this.render.linesLimit - 6)) * this.Q.B);
            this.render.move.page({
                offset: this.h.B - (this.render.linesLimit - 6)
            })
        } else {
            this.render.move.page()
        }
        this.caret.refocus(__f, this.h.B + 1, __f);
        this.b = __f;
        this.render.D.minHeight();
        this.render.D.scrollWidth()
    }
    q(__U) {
        let B = this.get.B(this.h.C);
        if (B.nodeName != "P") throw new Error("Parent has wrong tag, can't merge lines");
        if (__U < __f) {
            this.h.B;
            --;
            this.y(1, __f);
            this.render.__S[this.h.B].__S = this.render.__S[this.h.B].__S.concat(this.render.__S[this.h.B + 1].__S);
            this.render.__S.splice(this.h.B + 1, 1);
            this.b = this.get.realPos().x
        } else if (__U > __f) {
            this.render.__S[this.h.B].__S = this.render.__S[this.h.B].__S.concat(this.render.__S[this.h.B + 1].__S);
            this.render.__S.splice(this.h.B + 1, 1)
        }
        this.render.D.minHeight();
        this.render.D.scrollWidth()
    }
    v(z) {
        let __Q = this.replace.spaceChars(this.render.__S[this.h.B].__S[this.h.childIndex].__S);
        __Q = {
            __V: __Q.substr(__f, this.h.A),
            suf: __Q.substr(this.h.A)
        };
        __Q = this.replace.spaces(__Q.__V) + z + this.replace.spaces(__Q.suf);
        this.render.__S[this.h.B].__S[this.h.childIndex].__S = __Q;
        this.caret.refocus(this.h.A + this.replace.spaceChars(z).length);
        this.b = this.get.realPos().x
    }
    µ(_z) {
        if (!this._z) {
            return
        }
        if (!this.d) {
            let __W = (_y.clipboardData || window.clipboardData).getData('text');
            this.c = this.truck.exportText(__W)
        }
        this.action.__W()
    }
}
export {
    a
};
