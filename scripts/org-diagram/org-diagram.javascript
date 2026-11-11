class OrgChartDiagram extends Tag {
  setup() {
    this.diagram_id = router$.params.tab || router$.params.subpage;
    this.margin = { top: 40, right: 80, bottom: 40, left: 80 };
  }

  render() {
    this.setAttribute('loading', true);
    this.setAttribute('error', null);
    this.setAttribute('diagram', null);

    this.svg = null;
    this.loadData();
  }

  async loadData() {
    try {
      const result = await resource.action('load-org-data', {
        diagram_id: this.diagram_id
      });

      if (result.status === 'success') {
        this.setAttribute('diagram', result.data.tree);
        this.renderChart(result.data.tree);
      } else {
        throw new Error(result.message);
      }
    } catch (error) {
      this.setAttribute('error', error.message);
    } finally {
      this.setAttribute('loading', false);
    }
  }

  renderChart(data) {
    const width = this.chartContainer.clientWidth;
    const height = 500;

    this.chartContainer.innerHTML = '';

    this.svg = d3.select(this.chartContainer)
      .append('svg')
      .attr('width', width)
      .attr('height', height)
      .append('g')
      .attr('transform', `translate(${this.margin.left},${this.margin.top})`);

    const root = d3.hierarchy(data);
    const treeLayout = d3.tree().size([width - this.margin.left - this.margin.right, height - this.margin.top - this.margin.bottom]);
    treeLayout(root);

    // Links
    this.svg.selectAll('path.link')
      .data(root.links())
      .enter()
      .append('path')
      .attr('class', 'link')
      .attr('fill', 'none')
      .attr('stroke', '#555')
      .attr('stroke-width', 2)
      .attr('d', d3.linkVertical()
        .x(d => d.x)
        .y(d => d.y));

    // Nodes
    const node = this.svg.selectAll('g.node')
      .data(root.descendants())
      .enter()
      .append('g')
      .attr('class', 'node')
      .attr('transform', d => `translate(${d.x},${d.y})`)
      .style('cursor', 'pointer')
      .on('click', (event, d) => this.toggleCollapse(d));

    const boxWidth = 120;
    const boxHeight = 60;

    node.append('rect')
      .attr('x', -boxWidth / 2)
      .attr('y', -boxHeight / 2)
      .attr('width', boxWidth)
      .attr('height', boxHeight)
      .attr('rx', 10)
      .attr('ry', 10)
      .attr('fill', '#f2f2f2')
      .attr('stroke', '#999')
      .attr('stroke-width', 1.5);

    node.append('text')
      .attr('y', -10)
      .attr('text-anchor', 'middle')
      .style('font-size', '12px')
      .style('font-weight', 'bold')
      .text(d => d.data.name);

    node.append('text')
      .attr('y', 8)
      .attr('text-anchor', 'middle')
      .style('font-size', '11px')
      .style('fill', '#666')
      .text(d => d.data.title || '');

    node.append('text')
      .attr('y', 24)
      .attr('text-anchor', 'middle')
      .style('font-size', '10px')
      .style('fill', '#999')
      .text(d => d.data.subtitle || '');
  }

  toggleCollapse(d) {
    if (d.children) {
      d._children = d.children;
      d.children = null;
    } else {
      d.children = d._children;
      d._children = null;
    }
    this.renderChart(this.getAttribute('diagram'));
  }
}