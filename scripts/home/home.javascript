class Home extends Tag {
    // Configurable Constants
    static CONFIG = {
        MIN_BOX_SIZE: 100,
        MAX_BOX_SIZE: 100,
        BOX_SCALE_FACTOR: 18,
        ICON_SCALE: 0.618,
        FONT_SIZE: 18,
        GAP_FACTOR: 1.618,
        TEXT_OFFSET: 12,
        BORDER_RADIUS: 12,
        HOVER_DARKEN: 0.7,     // Darker hover effect (was 0.85)
        ACTIVE_DARKEN: 0.5,    // Even darker when clicked (was 0.75)
        PRESS_SHRINK: 5,       // How much smaller when pressed
        PRESS_OFFSET: 3,       // How much to move down and right
        ANIMATION_DURATION: 500 // Animation duration in milliseconds
    };

    setup() {
        this.hoveredIndex = null;
        this.iconCache = {};
        this.whiteIconCache = {};
        this.animationStates = {}; // Track animation state for each box
        this.mousePressed = false; // Track if mouse is currently pressed
        this.pressedIndex = null;  // Track which box is currently pressed
    }

    static GRADIENTS = [
        "#5e8bbd, #7aa3cc", "#d46481, #db829a", "#54998f, #70b1aa", "#579cc2, #7fb2cc",
        "#6f789d, #8b92b2", "#749b75, #8aae87", "#c37a68, #d49a85", "#7c6e9b, #9887b2",
        "#654ea3, #7c6e9b", "#3291c2, #579cc2", "#329ea3, #549fa6", "#558a5a, #749b75",
        "#3ca861, #15a3a8", "#c38c34, #d4a856", "#328077, #54998f", "#4e5d9e, #6f789d",
        "#4083c2, #5e8bbd", "#c23f6a, #d46481", "#329ea3, #549fa6", "#8749a1, #9e6ab2",
        "#746259, #8b807a", "#647886, #8797a0", "#7ea35a, #99bf74", "#a6ae50, #b3bb72",
        "#c26345, #d48464", "#d46481, #db829a", "#654ea3, #7c6e9b", "#328077, #54998f",
        "#3291c2, #579cc2", "#558a5a, #749b75"
    ];

    init() {
        GlobalResource.action('menu', 'get_menu',{usergroup_id:loggedInUser$.usergroup_id,language:translate('current_language')},true).then(result => {
            result = result.filter(obj => obj.name !== "home");
            this.menu = result;
            
            // Preload all icons before drawing
            this.preloadIcons().then(() => {
                this.drawMenu();
                
                // Attach event listeners after icons are loaded
                this.canvas.addEventListener('mousemove', this.handleMouseMove.bind(this));
                this.canvas.addEventListener('mousedown', this.handleMouseDown.bind(this));
                this.canvas.addEventListener('mouseup', this.handleMouseUp.bind(this));
                this.canvas.addEventListener('mouseleave', this.handleMouseLeave.bind(this));
            });
        });
        
        window.addEventListener('resize', () => {
            if (this.canvas) {
                this.drawMenu();
            }
        });
        
        const observer = new MutationObserver(() => {
            console.log('DOM changed, redrawing...');
            this.drawMenu();
        });
    }

    preloadIcons() {
        const promises = this.menu.map(item => {
            return new Promise((resolve) => {
                const img = new Image();
                img.src = '/uploads/icon-big/' + item.icon_big;
                img.crossOrigin = "Anonymous";
                
                img.onload = () => {
                    const dpr = window.devicePixelRatio || 1;
                    const maxIconSize = Home.CONFIG.MAX_BOX_SIZE * Home.CONFIG.ICON_SCALE;
                    
                    let drawWidth, drawHeight;
                    if (img.width > img.height) {
                        drawWidth = maxIconSize;
                        drawHeight = (img.height / img.width) * maxIconSize;
                    } else {
                        drawHeight = maxIconSize;
                        drawWidth = (img.width / img.height) * maxIconSize;
                    }
                    
                    drawWidth *= dpr;
                    drawHeight *= dpr;
                    
                    // Cache original colored icon
                    const colorCanvas = document.createElement("canvas");
                    colorCanvas.width = drawWidth;
                    colorCanvas.height = drawHeight;
                    const colorCtx = colorCanvas.getContext("2d");
                    colorCtx.drawImage(img, 0, 0, drawWidth, drawHeight);
                    
                    this.iconCache[item.icon] = {
                        canvas: colorCanvas,
                        width: drawWidth,
                        height: drawHeight
                    };
                    
                    // Create and cache white version
                    const whiteCanvas = document.createElement("canvas");
                    whiteCanvas.width = drawWidth;
                    whiteCanvas.height = drawHeight;
                    const whiteCtx = whiteCanvas.getContext("2d");
                    
                    whiteCtx.drawImage(img, 0, 0, drawWidth, drawHeight);
                    
                    const imageData = whiteCtx.getImageData(0, 0, drawWidth, drawHeight);
                    const data = imageData.data;
                    
                    for (let i = 0; i < data.length; i += 4) {
                        if (data[i + 3] > 0) {
                            data[i] = 255;
                            data[i + 1] = 255;
                            data[i + 2] = 255;
                        }
                    }
                    
                    whiteCtx.putImageData(imageData, 0, 0);
                    
                    this.whiteIconCache[item.icon] = {
                        canvas: whiteCanvas,
                        width: drawWidth,
                        height: drawHeight
                    };
                    
                    resolve();
                };
                
                img.onerror = () => resolve();
            });
        });
        
        return Promise.all(promises);
    }

    render() {
        window.setTimeout(function () {
            this.drawMenu();
        }.bind(this), 10);
    }

    handleMouseMove(event) {
        const { offsetX, offsetY } = event;
        const index = this.getHoveredItemIndex(offsetX, offsetY);

        if (this.hoveredIndex !== index && !this.mousePressed) {
            const prevIndex = this.hoveredIndex;
            this.hoveredIndex = index;

            if (prevIndex !== null && prevIndex !== this.pressedIndex) {
                this.restoreOriginalBox(prevIndex);
            }
            if (index !== null && index !== this.pressedIndex) {
                this.applyHoverEffect(index);
            }
        }
    }

    handleMouseDown(event) {
        if (this.hoveredIndex !== null) {
            this.mousePressed = true;
            this.pressedIndex = this.hoveredIndex;
            this.animatePressDown(this.hoveredIndex);
        }
    }

    handleMouseUp(event) {
        this.mousePressed = false;
        if (this.pressedIndex !== null) {
            this.clickedItem(this.menu[this.pressedIndex]);
            this.animatePressUp(this.pressedIndex);
        }
    }

    handleMouseLeave() {
        this.mousePressed = false;
        if (this.hoveredIndex !== null && this.hoveredIndex !== this.pressedIndex) {
            this.restoreOriginalBox(this.hoveredIndex);
        }
        if (this.pressedIndex !== null) {
            this.animatePressUp(this.pressedIndex);
        }
        this.hoveredIndex = null;
    }

    getHoveredItemIndex(x, y) {
        const boxSize = Math.min(Math.max(Home.CONFIG.MIN_BOX_SIZE, this.canvas.clientWidth / Home.CONFIG.BOX_SCALE_FACTOR), Home.CONFIG.MAX_BOX_SIZE);
        const cols = Math.floor(this.canvas.clientWidth / (boxSize * Home.CONFIG.GAP_FACTOR));
        const gap = (this.canvas.clientWidth - (cols * boxSize)) / (cols + 1);
        const verticalGap = gap;

        for (let index = 0; index < this.menu.length; index++) {
            const col = index % cols;
            const row = Math.floor(index / cols);
            const itemX = gap + col * (boxSize + gap);
            const itemY = gap + row * (boxSize + 8 + verticalGap);

            if (x >= itemX && x <= itemX + boxSize && y >= itemY && y <= itemY + boxSize) {
                return index;
            }
        }
        return null;
    }

    applyHoverEffect(index, isActive = false) {
        const ctx = this.canvas.getContext('2d');
        const menuItem = this.menu[index];
        const boxSize = Math.min(Math.max(Home.CONFIG.MIN_BOX_SIZE, this.canvas.clientWidth / Home.CONFIG.BOX_SCALE_FACTOR), Home.CONFIG.MAX_BOX_SIZE);
        const cols = Math.floor(this.canvas.clientWidth / (boxSize * Home.CONFIG.GAP_FACTOR));
        const row = Math.floor(index / cols);
        const col = index % cols;
        const gap = (this.canvas.clientWidth - (cols * boxSize)) / (cols + 1);
        const verticalGap = gap;

        const x = gap + col * (boxSize + gap);
        const y = gap + row * (boxSize + 8 + verticalGap);

        this.drawHoverBox(ctx, x, y, boxSize, index, isActive);
        this.drawIcon(ctx, x, y, boxSize, menuItem.icon, true);
        this.drawLabel(ctx, x, y + boxSize + Home.CONFIG.TEXT_OFFSET, boxSize, menuItem.title_translated);
    }

    restoreOriginalBox(index) {
        const ctx = this.canvas.getContext('2d');
        const menuItem = this.menu[index];
        const boxSize = Math.min(Math.max(Home.CONFIG.MIN_BOX_SIZE, this.canvas.clientWidth / Home.CONFIG.BOX_SCALE_FACTOR), Home.CONFIG.MAX_BOX_SIZE);
        const cols = Math.floor(this.canvas.clientWidth / (boxSize * Home.CONFIG.GAP_FACTOR));
        const row = Math.floor(index / cols);
        const col = index % cols;
        const gap = (this.canvas.clientWidth - (cols * boxSize)) / (cols + 1);
        const verticalGap = gap;

        const x = gap + col * (boxSize + gap);
        const y = gap + row * (boxSize + 8 + verticalGap);

        // Clear the area first to remove any shadows
        ctx.clearRect(x - 25, y - 25, boxSize + 50, boxSize + 50);

        this.drawSquare(ctx, x, y, boxSize, index);
        this.drawIcon(ctx, x, y, boxSize, menuItem.icon, true);
        this.drawLabel(ctx, x, y + boxSize + Home.CONFIG.TEXT_OFFSET, boxSize, menuItem.title_translated);
    }

    animatePressDown(index) {
        if (this.animationStates[index]) {
            clearInterval(this.animationStates[index].interval);
        }
        
        this.animationStates[index] = { 
            progress: 0, 
            direction: 'down',
            startTime: Date.now()
        };
        
        this.animatePress(index);
    }

    animatePressUp(index) {
        if (this.animationStates[index]) {
            clearInterval(this.animationStates[index].interval);
        }
        
        this.animationStates[index] = { 
            progress: 1, 
            direction: 'up',
            startTime: Date.now()
        };
        
        this.animatePress(index);
        this.pressedIndex = null;
    }

    animatePress(index) {
        const state = this.animationStates[index];
        if (!state) return;

        const animate = () => {
            const elapsed = Date.now() - state.startTime;
            const progress = Math.min(elapsed / Home.CONFIG.ANIMATION_DURATION, 1);
            
            let animationProgress;
            if (state.direction === 'down') {
                animationProgress = progress;
                state.progress = progress;
            } else {
                animationProgress = 1 - progress;
                state.progress = 1 - progress;
            }

            this.drawPressedBox(index, animationProgress);

            if (progress < 1) {
                requestAnimationFrame(animate);
            } else {
                if (state.direction === 'up') {
                    delete this.animationStates[index];
                    this.restoreOriginalBox(index);
                } else {
                    // Animation down complete, stay in pressed state if mouse is still down
                    if (this.mousePressed && this.pressedIndex === index) {
                        this.drawPressedBox(index, 1);
                    }
                }
            }
        };

        requestAnimationFrame(animate);
    }

    drawPressedBox(index, progress) {
        const ctx = this.canvas.getContext('2d');
        const menuItem = this.menu[index];
        const boxSize = Math.min(Math.max(Home.CONFIG.MIN_BOX_SIZE, this.canvas.clientWidth / Home.CONFIG.BOX_SCALE_FACTOR), Home.CONFIG.MAX_BOX_SIZE);
        const cols = Math.floor(this.canvas.clientWidth / (boxSize * Home.CONFIG.GAP_FACTOR));
        const row = Math.floor(index / cols);
        const col = index % cols;
        const gap = (this.canvas.clientWidth - (cols * boxSize)) / (cols + 1);
        const verticalGap = gap;

        const baseX = gap + col * (boxSize + gap);
        const baseY = gap + row * (boxSize + 8 + verticalGap);
        
        // Calculate animation progress with easing
        const easeProgress = progress * progress * (3 - 2 * progress); // Smooth step easing
        const shrink = easeProgress * Home.CONFIG.PRESS_SHRINK;
        const offsetX = easeProgress * Home.CONFIG.PRESS_OFFSET;
        const offsetY = easeProgress * Home.CONFIG.PRESS_OFFSET;
        
        const animatedX = baseX + offsetX;
        const animatedY = baseY + offsetY;
        const animatedSize = boxSize - shrink;
        
        // Clear the area
        ctx.clearRect(baseX - 25, baseY - 25, boxSize + 50, boxSize + 50);
        
        // Draw animated box
        this.drawAnimatedBox(ctx, animatedX, animatedY, animatedSize, index, easeProgress);
        this.drawIconScaled(ctx, animatedX, animatedY, animatedSize, menuItem.icon, true, animatedSize / boxSize);
        this.drawLabel(ctx, baseX, baseY + boxSize + Home.CONFIG.TEXT_OFFSET, boxSize, menuItem.title_translated);
    }

    drawMenu() {
        if (!this.menu || !this.canvas) return;

        const dpr = window.devicePixelRatio || 1;

        const oldWidth = this.canvas.width;
        const oldHeight = this.canvas.height;

        const newWidth = this.canvas.clientWidth * dpr;
        const newHeight = this.canvas.clientHeight * dpr;

        if (oldWidth !== newWidth || oldHeight !== newHeight) {
            this.canvas.width = newWidth;
            this.canvas.height = newHeight;
        }

        const ctx = this.canvas.getContext('2d');
        ctx.scale(dpr, dpr);

        if (oldWidth !== newWidth || oldHeight !== newHeight) {
            ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
        }

        const width = this.canvas.clientWidth;
        const height = this.canvas.clientHeight;

        const boxSize = Math.min(Math.max(Home.CONFIG.MIN_BOX_SIZE, width / Home.CONFIG.BOX_SCALE_FACTOR), Home.CONFIG.MAX_BOX_SIZE);
        const cols = Math.floor(width / (boxSize * Home.CONFIG.GAP_FACTOR));
        const rows = Math.ceil(this.menu.length / cols);
        const gap = (width - (cols * boxSize)) / (cols + 1);
        const verticalGap = gap;

        let startX = gap;
        let startY = gap;

        this.menu.forEach((item, index) => {
            const col = index % cols;
            const row = Math.floor(index / cols);

            const x = startX + col * (boxSize + gap);
            const y = startY + row * (boxSize + 8 + verticalGap);

            this.drawSquare(ctx, x, y, boxSize, index);
            this.drawIcon(ctx, x, y, boxSize, item.icon, true);
            this.drawLabel(ctx, x, y + boxSize + Home.CONFIG.TEXT_OFFSET, boxSize, item.title_translated);
        });
    }

    drawSquare(ctx, x, y, size, index) {
        const gradient = ctx.createLinearGradient(x, y, x + size, y + size);

        if (!Home.GRADIENTS || Home.GRADIENTS.length === 0) {
            console.error("Home.GRADIENTS is empty. Using default colors.");
            Home.GRADIENTS = ["#cccccc, #aaaaaa"];
        }

        const gradientEntry = Home.GRADIENTS[index % Home.GRADIENTS.length];

        if (!gradientEntry || typeof gradientEntry !== "string" || !gradientEntry.includes(",")) {
            console.warn(`Invalid gradient format at index ${index}:`, gradientEntry);
            return;
        }

        const colors = gradientEntry.split(", ").map(color => color.trim());

        // Draw shadows first (behind the box)
        // Outer shadow (20px blur)
       /* ctx.shadowColor = '#7f96b5';
        ctx.shadowBlur = 20;
        ctx.shadowOffsetX = 0;
        ctx.shadowOffsetY = 0;
        */
        gradient.addColorStop(0, colors[0]);
        gradient.addColorStop(1, colors[1]);
        ctx.fillStyle = gradient;
        
        ctx.beginPath();
        ctx.roundRect(x, y, size, size, Home.CONFIG.BORDER_RADIUS);
        ctx.fill();
        /*
        // Inner shadow (5px blur)
        ctx.shadowColor = '#7f96b5';
        ctx.shadowBlur = 5;
        ctx.shadowOffsetX = 0;
        ctx.shadowOffsetY = 0;
        
        // Draw again for inner shadow
        ctx.beginPath();
        ctx.roundRect(x, y, size, size, Home.CONFIG.BORDER_RADIUS);
        ctx.fill();*/
        
        // Reset shadows
        ctx.shadowColor = 'transparent';
        ctx.shadowBlur = 0;
        ctx.shadowOffsetX = 0;
        ctx.shadowOffsetY = 0;

        // Draw border (darker version of gradient colors)
        const borderGradient = ctx.createLinearGradient(x, y, x + size, y + size);
        borderGradient.addColorStop(0, this.darkenColor(colors[0], 0.85)); // Notably darker
        borderGradient.addColorStop(1, this.darkenColor(colors[1], 0.85)); // Notably darker
        
        ctx.strokeStyle = borderGradient;
        ctx.lineWidth = 1;
        ctx.beginPath();
        ctx.roundRect(x, y, size, size, Home.CONFIG.BORDER_RADIUS);
        ctx.stroke();
    }

    drawHoverBox(ctx, x, y, size, index, isActive = false) {
        const gradientEntry = Home.GRADIENTS[index % Home.GRADIENTS.length];
        const colors = gradientEntry.split(", ").map(color => color.trim());

        // Clear the area first to remove any previous shadows
        ctx.clearRect(x - 25, y - 25, size + 50, size + 50);

        // Draw shadows
        ctx.shadowColor = '#7f96b5';
        ctx.shadowBlur = 20;
        ctx.shadowOffsetX = 0;
        ctx.shadowOffsetY = 0;
        
        const gradient = ctx.createLinearGradient(x, y, x + size, y + size);
        const darkenFactor = isActive ? Home.CONFIG.ACTIVE_DARKEN : Home.CONFIG.HOVER_DARKEN;
        gradient.addColorStop(0, this.darkenColor(colors[0], darkenFactor));
        gradient.addColorStop(1, this.darkenColor(colors[1], darkenFactor));

        ctx.fillStyle = gradient;
        ctx.beginPath();
        ctx.roundRect(x, y, size, size, Home.CONFIG.BORDER_RADIUS);
        ctx.fill();
        
        // Inner shadow
        ctx.shadowBlur = 5;
        ctx.beginPath();
        ctx.roundRect(x, y, size, size, Home.CONFIG.BORDER_RADIUS);
        ctx.fill();
        
        // Reset shadows
        ctx.shadowColor = 'transparent';
        ctx.shadowBlur = 0;
        ctx.shadowOffsetX = 0;
        ctx.shadowOffsetY = 0;

        // Draw border (darker version of the already darkened colors)
        const borderGradient = ctx.createLinearGradient(x, y, x + size, y + size);
        borderGradient.addColorStop(0, this.darkenColor(colors[0], darkenFactor * 0.7)); // Darker than hover/active state
        borderGradient.addColorStop(1, this.darkenColor(colors[1], darkenFactor * 0.7)); // Darker than hover/active state
        
        ctx.strokeStyle = borderGradient;
        ctx.lineWidth = 1;
        ctx.beginPath();
        ctx.roundRect(x, y, size, size, Home.CONFIG.BORDER_RADIUS);
        ctx.stroke();
    }

    drawAnimatedBox(ctx, x, y, size, index, progress) {
        const gradientEntry = Home.GRADIENTS[index % Home.GRADIENTS.length];
        const colors = gradientEntry.split(", ").map(color => color.trim());
        
        // Animate shadow blur from 5px to 3px
        const shadowBlur = 5 - (progress * 2);
        
        // Draw shadows
        ctx.shadowColor = '#7f96b5';
        ctx.shadowBlur = 20;
        ctx.shadowOffsetX = 0;
        ctx.shadowOffsetY = 0;
        
        const gradient = ctx.createLinearGradient(x, y, x + size, y + size);
        gradient.addColorStop(0, this.darkenColor(colors[0], Home.CONFIG.ACTIVE_DARKEN));
        gradient.addColorStop(1, this.darkenColor(colors[1], Home.CONFIG.ACTIVE_DARKEN));
        
        ctx.fillStyle = gradient;
        ctx.beginPath();
        ctx.roundRect(x, y, size, size, Home.CONFIG.BORDER_RADIUS);
        ctx.fill();
        
        // Inner shadow
        ctx.shadowBlur = shadowBlur;
        ctx.beginPath();
        ctx.roundRect(x, y, size, size, Home.CONFIG.BORDER_RADIUS);
        ctx.fill();
        
        // Reset shadows
        ctx.shadowColor = 'transparent';
        ctx.shadowBlur = 0;
        ctx.shadowOffsetX = 0;
        ctx.shadowOffsetY = 0;

        // Draw border (darker version of the active colors)
        const borderGradient = ctx.createLinearGradient(x, y, x + size, y + size);
        borderGradient.addColorStop(0, this.darkenColor(colors[0], Home.CONFIG.ACTIVE_DARKEN * 0.7)); // Darker than active state
        borderGradient.addColorStop(1, this.darkenColor(colors[1], Home.CONFIG.ACTIVE_DARKEN * 0.7)); // Darker than active state
        
        ctx.strokeStyle = borderGradient;
        ctx.lineWidth = 1;
        ctx.beginPath();
        ctx.roundRect(x, y, size, size, Home.CONFIG.BORDER_RADIUS);
        ctx.stroke();
    }

    drawIcon(ctx, x, y, boxSize, iconPath, useWhite = true) {
        const cache = useWhite ? this.whiteIconCache : this.iconCache;
        const cached = cache[iconPath];
        
        if (!cached) return;
        
        const dpr = window.devicePixelRatio || 1;
        const centerX = x + (boxSize - cached.width / dpr) / 2;
        const centerY = y + (boxSize - cached.height / dpr) / 2;
        
        ctx.drawImage(cached.canvas, centerX, centerY, cached.width / dpr, cached.height / dpr);
    }

    drawIconScaled(ctx, x, y, boxSize, iconPath, useWhite = true, scale = 1) {
        const cache = useWhite ? this.whiteIconCache : this.iconCache;
        const cached = cache[iconPath];
        
        if (!cached) return;
        
        const dpr = window.devicePixelRatio || 1;
        const scaledWidth = (cached.width / dpr) * scale;
        const scaledHeight = (cached.height / dpr) * scale;
        const centerX = x + (boxSize - scaledWidth) / 2;
        const centerY = y + (boxSize - scaledHeight) / 2;
        
        ctx.drawImage(cached.canvas, centerX, centerY, scaledWidth, scaledHeight);
    }

    drawLabel(ctx, x, y, size, text) {
        const dpr = window.devicePixelRatio || 1;

        ctx.clearRect(x, y, size, Home.CONFIG.FONT_SIZE * dpr * 1.5);

        ctx.font = (Home.CONFIG.FONT_SIZE * dpr) + 'px sans-serif';
        ctx.fillStyle = "black";
        ctx.textAlign = "center";
        ctx.textBaseline = "top";
        ctx.fillText(text, x + size / 2, y);
    }

    darkenColor(color, factor) {
        if (!color.startsWith("#") || color.length !== 7) {
            console.warn("Unexpected color format:", color);
            return color;
        }

        const rgb = parseInt(color.slice(1), 16);
        const r = Math.floor(((rgb >> 16) & 0xff) * factor);
        const g = Math.floor(((rgb >> 8) & 0xff) * factor);
        const b = Math.floor((rgb & 0xff) * factor);
        return `rgb(${r}, ${g}, ${b})`;
    }

    clickedItem(menuItem) {
        if (menuItem && menuItem.url && menuItem.url !== '') {
            window.location = menuItem.url;
        } else {
            router$.goto('/' + menuItem.name);
        }
    }
}