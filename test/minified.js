class a {
    b = null;
    c() {
        this.action.b = document.createElement('div');
        this.render.overflow.insertBefore(this.action.b, this.editor);
    }
    d() {
        const f = this.get.selectedLines();
        const h = this.h.dispatch('tabJFCopy', {
            i: this.get.clonedPos(),
            h: null,
            f: this.get.clone(f)
        });
        if (h.defaultPrevented) return;
        this.f = this.get.clone(f);
        const j = this.action.b;
        this.truck.import(this.f, false, 0, false, false, j, false);
        let k = j.children[0].children[0].childNodes[0];
        let g = j.children[j.children.length - 1];
        g = g.children[g.children.length - 1];
        g = g.childNodes[g.childNodes.length - 1];
        const l = new Range;
        l.setStart(k, 0);
        l.setEnd(g, g.nodeValue.length);
        this.get.selection().removeAllRanges();
        this.get.selection().addRange(l);
        setTimeout(function() {
            document.execCommand('copy');
            this.m = true;
            j.n = '';
            this.checkSelect();
        }.bind(this), 0);
    }
    e() {
        const h = this.h.dispatch('tabJFPaste', {
            i: this.get.clonedPos(),
            h: null,
            f: this.get.clone(this.f)
        });
        if (h.defaultPrevented) return;
        this.remove.selected();
        const f = this.get.clone(this.f);
        const p = f[0];
        const r = f[f.length - 1];
        let s = this.render.o[this.i.line];
        let t = s.o[this.i.childIndex];
        let u = this.replace.spaceChars(t.o).substr(0, this.i.letter);
        let w = this.replace.spaceChars(t.o).substr(this.i.letter);
        t.o = u;
        let z = s.o.splice(this.i.childIndex + 1);
        s.o = s.o.concat(p.o);
        let y = this.get.clone(f.slice(1, f.length - 1)) let q, vif(f.length > 1) {
            let µ = f[f.length - 1];
            v = µ.o.length - 1;
            q = this.replace.spaceChars(µ.o[µ.o.length - 1].o).length;
            µ.o[µ.o.length - 1].o;
            o += w;
            µ.o = µ.o.concat(z);
            y = y.concat([µ]);
        } else {
            q = p.o[p.o.length - 1].o.length;
            v = this.i.childIndex + p.o.length;
            s.o[s.o.length - 1].o;
            o += w;
            s.o = s.o.concat(z);
        }
        this.render.o.splice(this.i.line + 1, 0, ...y) this.render.move.page() this.render.set.overflow(null, ((this.i.line + f.length - 1) - (Math.floor(this.render.linesLimit / 2))) * this.settings.line) this.caret.refocus(q, this.i.line + f.length - 1, v) this.x = this.get.realPos().xthis.render.update.minHeight() this.render.update.scrollWidth() this.update.selection.start() this.update.page()
    }
    cut() {
        consth = this.h.dispatch('tabJFCut', {
            i: this.get.clonedPos(),
            h: null,
            f: this.get.clone(this.f)
        }) if (h.defaultPrevented) returnthis.action.d() this.remove.selected() this.render.update.minHeight() this.render.update.scrollWidth()
    }
    undo {
        const ê = this.get.clone(this._save.versions[this._save.A] ? ? {});
        const õ = this._save.A;
        const h = this.h.dispatch('tabJFUndo', {
            i: this.get.clonedPos(),
            h: null,
            ú: this._save.A - 1,
            A: this.get.clone(this._save.versions[this._save.A - 1] ? ? {}),
            versionNumberBefor,
            versionBefor
        });
        if (h.defaultPrevented) return;
        this._save.restore();
        this.x = this.get.realPos().x;
        this.render.update.minHeight();
        this.render.update.scrollWidth();
    }
    while () {
        constê = this.get.clone(this._save.versions[this._save.A] ? ? {}) constõ = this._save.Aconsth = this.h.dispatch('tabJFRedo', {
            i: this.get.clonedPos(),
            h: null,
            ú: this._save.A + 1,
            A: this.get.clone(this._save.versions[this._save.A + 1] ? ? {}),
            versionNumberBefor,
            ê
        }) if (h.defaultPrevented) returnthis._save.recall() this.x = this.get.realPos().xthis.render.update.minHeight() this.render.update.scrollWidth()
    }
    selectAll() {
        consth = this.h.dispatch('tabJFSelectAll', {
            i: this.get.clonedPos(),
            h: null
        }) if (h.defaultPrevented) returnthis.update.selection.start(0, 0, 0) constr = this.render.o[this.render.o.length - 1] constB = r.o[r.o.length - 1] constC = this.replace.spaceChars(B.o) this.update.selection.end(C.length, this.render.o.length - 1, r.o.length - 1) this.selection.D = true this.checkSelect()
    };
    export {
        a
    };
}
}
