class LineDiagram extends Tag {
    setup() {
        this.partner_id = router$.params.partner_id;
        this.margin = { top: 20, right: 0, bottom: 30, left: 40 };
        this.colors = d3.scaleOrdinal(d3.schemeCategory10);
    }

    render() {
        this.setAttribute('loading', true);
        this.setAttribute('error', null);
        this.setAttribute('diagram', null);
        this.svg = null;
        this.xScale = null;
        this.yScale = null;
        this.line = null;

        this.loadData();
        this.setupResizeHandler();
    }

    async loadData() {
        try {
            const result = await resource.action('load-data', {
                partner_id: this.partner_id
            });

            if (result.status === 'success') {
                this.setAttribute('diagram', result.data.diagram);
                this.seriesData = result.data.series;
                this.initializeChart();
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            this.setAttribute('error', error.message);
        } finally {
            this.setAttribute('loading', false);
        }
    }

initializeChart() {
    const width = this.chartContainer.clientWidth - this.margin.left - this.margin.right;
    const height = 300 - this.margin.top - this.margin.bottom;

    this.chartContainer.innerHTML = '';

    this.svg = d3.select(this.chartContainer)
        .append('svg')
        .attr('width', width + this.margin.left + this.margin.right)
        .attr('height', height + this.margin.top + this.margin.bottom)
        .append('g')
        .attr('transform', `translate(${this.margin.left},${this.margin.top})`);

    this.seriesData[0].values.forEach(d => {
        d.x = new Date(d.x);
        d.y = +d.y;
    });

    const data = this.seriesData[0].values;

    this.xScale = d3.scaleBand()
        .domain(data.map(d => d.x))
        .range([0, width])
        .padding(0.1); // etwas Platz zwischen den Balken

    this.yScale = d3.scaleLinear()
        .domain([0, d3.max(data, d => d.y)])
        .nice()
        .range([height, 0]);

    // Achsen
    this.svg.append('g')
        .attr('transform', `translate(0,${height})`)
        .call(d3.axisBottom(this.xScale).tickFormat(d3.timeFormat('%d.%m')))
        .selectAll("text")
        .attr("transform", "rotate(-45)")
        .style("text-anchor", "end");

    this.svg.append('g')
        .call(d3.axisLeft(this.yScale));

    // Balken
    this.svg.selectAll('.bar')
        .data(data)
        .enter()
        .append('rect')
        .attr('class', 'bar')
        .attr('x', d => this.xScale(d.x))
        .attr('y', d => this.yScale(d.y))
        .attr('width', this.xScale.bandwidth())
        .attr('height', d => height - this.yScale(d.y))
        .attr('fill', this.colors(this.seriesData[0].name));
}


    setupResizeHandler() {
        const resizeObserver = new ResizeObserver(() => {
            if (this.svg) this.initializeChart();
        });
        resizeObserver.observe(this.chartContainer);
    }
}
