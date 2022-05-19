class a {
    b(v) {
        return JSON.parse(JSON.stringify(v));
    }
    c() {
        const ê = Object.assign({}, this.ê);
        ê.µ = this.ê.µ;
        return ê;
    }
    d() {
        return this;
    }
    e() {
        return this.render.E.slice(this.render.hidden, this.render.hidden + this.render.linesLimit);
    }
    f(õ = null, ú = null) {
        if (!õ || !ú) {
            const A = this.get.k(),
                B = this.k.reverse && !this.k.expanded;
            õ = this.get.m(B ? A.focusNode : A.anchorNode);
            ú = this.get.m(B ? A.anchorNode : A.focusNode);
        }
        if (!õ || !ú) throw new Error('Couldn\'t find lines');
        return [õ.cloneNode(true), ...this.get.g(õ.nextSibling, ú)];
    }
    g(C, D) {
        if (C === null) throw new Error('The node doesn\'t exist in this parent');
        if (C == D) return [C.cloneNode(true)];
        if (C.nodeName !== "P") return this.get.g(C.nextSibling, D);
        return [C.cloneNode(true), ...this.get.g(C.nextSibling, D)];
    }
    h() {
        const A = this.get.k();
        if (A.type != 'Range') return;
        let F = this.get.b(this.k.F);
        let D = this.get.b(this.k.D);
        if (F.m > D.m || (F.m == D.m && F.C > D.C) || (F.m == D.m && F.C == D.C && F._h > D._h)) {
            let G = F;
            F = D;
            D = G;
        }
        if (F.m == D.m) {
            const m = this.get.b(this.render.E[F.m]);
            delete m.ends;
            delete m.groupPath;
            if (F.C == D.C) {
                let E = this.replace.spaceChars(m.E[F.C].E);
                let H = this.replace._g(E.substr(F._h, D._h - F._h));
                m.E = [this.syntax.create._d({}, H)];
                return [m];
            } else {
                let I = m.E[F.C];
                let J = m.E[D.C];
                I.E = this.replace._g(this.replace.spaceChars(I.E).substr(F._h));
                J.E = this.replace._g(this.replace.spaceChars(J.E).substr(0, D._h));
                m.E = [I].concat(m.E.slice(F.C + 1, D.C + 1));
                return [m];
            }
        }
        let K = this.render.E.slice(F.m + 1, D.m);
        let L = this.get.b(this.render.E[F.m]);
        let M = this.get.b(this.render.E[D.m]);
        M.E = M.E.slice(0, D.C + 1);
        let N = M.E[M.E.length - 1];
        N.E = N.E.replaceAll('&nbsp;', ' ');
        N.E = N.E.substr(0, D._h);
        N.E = N.E.replaceAll(' ', '&nbsp;');
        L.E = L.E.slice(F.C);
        let I = L.E[0];
        I.E = I.E.replaceAll('&nbsp;', ' ');
        I.E = I.E.substr(F._h);
        I.E = I.E.replaceAll(' ', '&nbsp;');
        return [L].concat(K, [M]);
    }
    i(µ) {
        for (let O = 0; O < µ.parentElement.R.length; O++) {
            if (µ.parentElement.R[O] == µ) return O;
        }
        return false;
    }
    j(m) {
        let j = 0;
        for (let O = 0; O < this.editor.R.length; O++) {
            let P = this.editor.R[O];
            if (m == P) return j;
            if (P.nodeName && P.nodeName == "P") j++;
        }
        return false;
    }
    k() {
        return window.getSelection ? window.getSelection() : document.k;
    }
    l() {
        const R = Object.values(this.ê.µ.parentElement.R);
        let Q = 0;
        for (let O = 0; O < R.length; O++) {
            if (this.ê.µ == R[O]) break;
            Q += R[O].innerText.length;
        }
        Q += this.ê._h;
        return {
            S: Q,
            T: this.ê.m
        };
    }
    m(µ) {
        if (!µ.parentElement) return false;
        if (µ.parentElement == this.editor) return µ;
        return this.get.m(µ.parentElement);
    }
    n(ê) {
        ê -= this.render.hidden;
        if (ê >= 0) {
            let j = -1;
            for (var O = 0; O < this.editor.R.length; O++) {
                let m = this.editor.R[O];
                if (m.nodeName == "P") j++;
                if (j == ê) return m;
            }
        } else {
            let j = 0;
            for (var O = this.editor.R.length - 1; O > -1; O--) {
                let m = this.editor.R[O];
                if (m.nodeName == "P") j++;
                if (j == ê * -1) return m;
            }
        }
        return false;
    }
    o(m, U, V = true) {
        if (V && m?.nodeName != "P") throw new Error("Parent has wrong tag, can't find proper lines");
        if (!V && m?.nodeName == "P") return m;
        let W;
        if (m === null) return m;
        if (U < 0) W = m.previousSibling;
        else if (U > 0) W = m.nextSibling;
        if (W === null) return W;
        if (W.nodeType != 1) {
            let X;
            if (U < 0) X = W.previousSibling;
            else if (U > 0) X = W.nextSibling;
            W.remove();
            return this.get.o(X, U, false);
        }
        if (W.nodeName != "P") return this.get.o(W, U, false);
        if (U == -1 || U == 1) return W;
        return this.get.o(W, U < 0 ? U + 1 : U - 1, true);
    }
    p(C, U) {
        if (U > 0) return C.nextSibling;
        else if (U < 0) return C.previousSibling;
    }
    r(µ) {
        for (var O = 0; O < µ.parentElement.childNodes.length; O++) {
            if (µ.parentElement.childNodes[O] == µ) return O;
        }
        return false;
    }
    s(µ) {
        const Y = {};
        for (let Z, O = 0, Ī = µ.s, $ = Ī.length; O < $; O++) {
            Z = Ī[O];
            Y[Z.nodeName] = Z.nodeValue;
        }
        return Y;
    }
    t(ê = this.ê._h) {
        let H = this.ê.µ.innerText;
        return {
            _: this.set.s(this.ê.µ.s, H.substr(0, ê)),
            _a: this.set.s(this.ê.µ.s, H.substr(ê))
        };
    }
    u(µ = this.ê.µ, ê = this.ê._h) {
        let _b = this.get.t(ê);
        let _c = this.get.w(µ.nextSibling);
        _b._a = [_b._a, ..._c];
        return _b;
    }
    w(µ) {
        if (µ === null) return [];
        let _c = [];
        let _d = this.set.s(µ.s, µ.innerText);
        _c.push(_d);
        if (µ.nextSibling) {
            let _e = this.get.w(µ.nextSibling);
            _c = _c.concat(_e);
        }
        µ.remove();
        return _c;
    }
    z(m) {
        let y = '';
        m.E.forEach(_d => {
            y += _d.E;
        });
        return this.replace.spaceChars(y);
    }
    y(z) {
        let _f = '';
        y = [];
        let _g = false;
        if (this.is.space(z[0])) _g = true;
        for (let O = 0; O < z.length; O++) {
            const _h = z[O];
            const _i = this.is.space(_h);
            if (_i && _g == false || !_i && _g == true) {
                y.push(_f);
                _f = _h;
            } else _f += _h;
            if (_i) _g = true;
            else _g = false;
        }
        y.push(_f);
        return y;
    }
    x() {
        return this.replace.spaceChars(this.render.E[this.ê.m].E[this.ê.r].E);
    }
    q(H, F = 0) {
        const _j = H.indexOf('\u00A0', F);
        if (_j !== -1) {
            return _j;
        }
        return H.indexOf(' ', F);
    }
}
export {
    a
};
