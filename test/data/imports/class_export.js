class TabJF_Render_Add {
  /**
   * Add line to the render and refresh editor
   * @param  {Node  } line
   * @param  {Number} pos  Index - where insert line
   */
  line ( line, pos ) {
    this.render.content.splice( pos, 0, this.truck.exportLine( line ) );
    this.render.fill  .event    ();
    this.render.update.minHeight();
  }
}
export { TabJF_Render_Add };
