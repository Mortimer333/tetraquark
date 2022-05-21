/*return this.caret.isActive
    && (
      this.pos.line >= this.render.hidden
      && this.pos.line <= this.render.hidden + this.render.linesLimit
    );

this.render.set.overflow(
  null,
  (
    (this.pos.line + clipboard.length - 1)
    - (Math.floor(this.render.linesLimit/2))
  ) * this.settings.line
);
*/
const results = target.bind( main )( ...args );
