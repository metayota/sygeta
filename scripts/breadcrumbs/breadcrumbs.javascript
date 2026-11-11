class Breadcrubs extends Tag {
    gotoBreadcrumb(breadcrumb) {
        router$.goto(breadcrumb.url)
        App.pushBreadcrumbs(true)
    }
}