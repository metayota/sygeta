class LineDiagram extends Tag {
    setup() {
        this.diagram_id = router$.params.tab || router$.params.subpage;
        this.margin = { top: 20, right: 20, bottom: 30, left: 50 }; // left margin for y-axis numbers
        this.colors = d3.scaleOrdinal(d3.schemeCategory10);
        this.y_unit = ''; // default y-axis unit
    }

    render() {
        this.setAttribute('loading', true);
        this.setAttribute('error', null);
        this.setAttribute('diagram', null);
        this.setAttribute('seriesOptions', []);

        this.svg = null;
        this.xScale = null;
        this.yScale = null;
        this.line = null;

        this.loadData();
        this.setupResizeHandler();
    }

    async loadData() {
        try {
            const result = await resource.action('load-data', { diagram_id: this.diagram_id });

            if (result.status === 'success') {
                this.setAttribute('diagram', result.data.diagram);
                this.processSeriesData(result.data.series);
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

    processSeriesData(series) {
        this.seriesData = series;
        const options = series.map(s => ({ value: s.name, label: s.name }));
        this.setAttribute('seriesOptions', options);
    }

    initializeChart() {
        this.chartContainer.innerHTML = '';

        const width = this.chartContainer.clientWidth - this.margin.left - this.margin.right;
        const height = this.chartContainer.clientHeight ? this.chartContainer.clientHeight - this.margin.top - this.margin.bottom : 200;

        this.xScale = d3.scaleLinear().range([0, width]);
        this.yScale = d3.scaleLinear().range([height, 0]);

        this.line = d3.line()
            .x(d => this.xScale(d.x))
            .y(d => this.yScale(d.y));

        this.svg = d3.select(this.chartContainer)
            .append('svg')
            .attr('width', this.chartContainer.clientWidth)
            .attr('height', this.chartContainer.clientHeight || 200)
            .append('g')
            .attr('transform', `translate(${this.margin.left},${this.margin.top})`);

        // Background
        this.svg.append('rect')
            .attr('width', width)
            .attr('height', height)
            .attr('fill', 'white');

        this.renderChart();
        this.renderLegend();
    }

    renderChart() {
        if (!this.svg || !this.seriesData) return;

        const allValues = this.seriesData.flatMap(s => s.values);
        this.xScale.domain(d3.extent(allValues, d => d.x));
        this.yScale.domain(d3.extent(allValues, d => d.y));

        this.svg.selectAll('.axis').remove();

        const width = this.chartContainer.clientWidth - this.margin.left - this.margin.right;
        const height = this.chartContainer.clientHeight ? this.chartContainer.clientHeight - this.margin.top - this.margin.bottom : 200;

        // X-axis (years as integers)
        this.svg.append('g')
            .attr('class', 'axis')
            .attr('transform', `translate(0,${height})`)
            .call(d3.axisBottom(this.xScale).ticks(5).tickFormat(d3.format("d")));

        // Y-axis with unit
        this.svg.append('g')
            .attr('class', 'axis')
            .call(d3.axisLeft(this.yScale).tickFormat(d => d + (this.y_unit || '')));

        // Lines
        const lines = this.svg.selectAll('.line')
            .data(this.seriesData, d => d.name);

        lines.enter()
            .append('path')
            .attr('class', 'line')
            .merge(lines)
            .attr('fill', 'none')
            .style('stroke', d => this.colors(d.name))
            .style('stroke-width', 2)
            .attr('d', d => this.line(d.values));

        lines.exit().remove();
    }

    renderLegend() {
        const legend = d3.select(this.legendContainer);
        legend.selectAll('*').remove();

        this.seriesData.forEach(series => {
            const item = legend.append('div')
                .attr('class', 'legend-item')
                .style('cursor', 'pointer')
                .on('click', () => this.toggleSeries(series.name));

            item.append('span')
                .style('background-color', this.colors(series.name))
                .style('display', 'inline-block')
                .style('width', '12px')
                .style('height', '12px')
                .style('margin-right', '5px');

            item.append('span')
                .text(series.name);
        });
    }

    toggleSeries(seriesName) {
        const lines = this.svg.selectAll('.line');
        lines.each(function(d) {
            if (d.name === seriesName) {
                const currentOpacity = d3.select(this).style('opacity');
                d3.select(this).style('opacity', currentOpacity === '1' ? 0 : 1);
            }
        });
    }

    setupResizeHandler() {
        const resizeObserver = new ResizeObserver(() => {
            if (!this.svg || !this.seriesData) return;

            const width = this.chartContainer.clientWidth - this.margin.left - this.margin.right;
            const height = this.chartContainer.clientHeight ? this.chartContainer.clientHeight - this.margin.top - this.margin.bottom : 200;

            this.xScale.range([0, width]);
            this.yScale.range([height, 0]);

            this.svg
                .attr('width', this.chartContainer.clientWidth)
                .attr('height', this.chartContainer.clientHeight || 200);

            this.svg.select('rect')
                .attr('width', width)
                .attr('height', height);

            this.renderChart();
        });

        resizeObserver.observe(this.chartContainer);
    }

    refreshData() {
        this.setAttribute('loading', true);
        this.setAttribute('error', null);
        this.setAttribute('diagram', null);
        this.setAttribute('seriesOptions', []);

        this.svg = null;
        this.xScale = null;
        this.yScale = null;
        this.line = null;

        this.loadData();
    }
}
