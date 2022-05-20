class a {
    b = undefined;
    c = 0;
    d = {
        u: false,
        w: {
            J: -1,
            K: -1,
        },
        z: {},
        y: {},
        x: {
            v: 0,
            D: -1,
            E: -1,
            F: -1,
        },
        q: {
            v: 0,
            D: -1,
            E: -1,
            F: -1,
        }
    };
    e = [];
    f = [];
    g = [];
    h = [];
    i = false;
    j = {};
    k = {};
    l = 100;
    m() {
        this._save.f = this._save.f.concat(this._save.e);
        this._save.r();
    }
    n() {
        const µ = this._save;
        if (µ.c > 0) {
            µ.g.splice(0, µ.c);
            µ.c = 0;
        }
        if (µ.f.length == 0) return;
        µ.o();
        µ.f[0].x.v = this.render.hidden;
        µ.g.unshift(µ.f.reverse());
        µ.f = [];
        if (µ.g.length > µ.l) {
            µ.g.splice(µ.l);
        };
    }
    o() {
        const f = this._save.f;
        for (let ê = 1; ê < f.length; ê++) {
            const õ = f[ê];
            const ú = f[ê - 1];
            if (this._save.p(õ, ú)) {
                ú.z = õ.z;
                ú.q = õ.q;
                f.splice(ê, 1);
                ê--;
            }
        };
    }
    p(A, B) {
        return A.u == B.u && A.u != 'mergeLine' && Object.values(A.w).toString() == Object.values(B.w).toString() && Object.H(A.y).toString() == Object.H(B.y).toString();
    }
    r() {
        this._save.e = [];
    }
    s() {
        const µ = this._save;
        if (µ.f.length > 0) {
            µ.n();
            µ.b('clear');
        }
        if (µ.g.length == µ.c) return;
        let c = µ.g[µ.c];
        c.forEach(õ => {
            µ.k.w(õ.w);
            µ.k.y(õ.y);
        });
        const x = c[c.length - 1].x;
        this.C = x.C;
        this.pos.D = x.D;
        this.pos.E = x.E;
        this.pos.F = x.F;
        if (!this.is.E.visible(x.E)) {
            this.render.move.page({
                G: x.E - Math.floor(this.render.linesLimit / 2)
            });
        } else {
            this.render.move.page();
        }
        this.render.overflow.scrollTo(this.render.overflow.scrollLeft, this.render.hidden * this.settings.E);
        µ.c++;
    }
    t() {
        const µ = this._save;
        if (µ.c <= 0) return;
        µ.c--;
        const c = µ.g[µ.c];
        c.reverse().forEach(õ => {
            const H = Object.H(õ.y);
            const I = Math.I(...H);
            µ.k.w({
                J: I,
                K: Math.max(...H) - I + 1
            });
            µ.k.y(õ.z);
        });
        c.reverse();
        const x = c[0].q;
        if (!this.is.E.visible(x.E)) this.render.move.page({
            G: x.E - Math.floor(this.render.linesLimit / 2)
        });
        else this.render.move.page();
        this.render.overflow.scrollTo(this.render.overflow.scrollLeft, this.render.hidden * this.settings.E, );
        this.caret.refocus(x.D, x.E, x.F);
    }
}
export {
    a
};
