<div class="sales-pipeline-overview">

    <h1><t>sales_pipeline_overview_title</t></h1>
    <div for="step of this.steps">
        <h2>{step_index+1}.) {step.title_translated}</h2>
        <p>{step.description_translated}</p>
        <p if="step.actions?.length > 0"><b><t>sales_pipeline_overview_actions</t></b></p>
        <div for="action of step.actions">
            <h3><img src="/images/icons/24-button-2.svg"/>{action.title_translated}</h3>
            <p>{action.description_translated}</p>  
            <div if="action.visualized_html">
                <innerhtml value="{action.visualized_html}"></innerhtml>
            </div>
        </div> 
        <hr/>
    </div>
</div>