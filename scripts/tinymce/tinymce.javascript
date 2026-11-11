class TinyMCETag extends Tag {
    static initialize() {
        TinyMCETag.textarea_count = 0;
    }

    init() {
        TinyMCETag.textarea_count++;
        this.textarea_id = 'tinymce_textarea_' + TinyMCETag.textarea_count;
        this.isInitialized = false; // Track initialization state
        this.ignoreChange = false;
        
        Tag.load('/node_modules/tinymce/tinymce.min.js').then(result => {
            window.handleEmojiClick = function(src, title) {
                tinymce.activeEditor.insertContent(`<img src="${src}" class="emoji" alt="${title}" style="width:21px;height:21px"/>`);
                tinymce.activeEditor.windowManager.close();
            };

            resource.call().then(customEmojis => {
                tinymce.init({
                    license_key: 'gpl',
                    selector: '#' + this.textarea_id,
                    height: 400,
                    
                    // Essential plugins only
                    plugins: [
                        'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                        'searchreplace', 'visualblocks', 'code', 'fullscreen',
                        'media', 'table', 'wordcount', 'help', 'codesample',
                        'emoticons', 'quickbars', 'formatpainter', 'customemoticons'
                    ],
                    
                    // Single line toolbar with everything
                    toolbar: 'undo redo | bold italic underline | formatpainter removeformat | fontfamily fontsize | forecolor backcolor | alignleft aligncenter alignright alignjustify | outdent indent | blocks | bullist numlist | table link image media codesample | customemoticons emoticons | searchreplace code fullscreen help',
                    
                    // Hide upgrade button and keep minimal menubar
                    menubar: false,
                    promotion: false,
                    branding: false,
                    
                    // Essential block formats
                    block_formats: 'Paragraph=p; Heading 1=h1; Heading 2=h2; Heading 3=h3; Heading 4=h4; Blockquote=blockquote',
                    
                    // Common font options
                    font_family_formats: 'Arial=arial,helvetica,sans-serif; Georgia=georgia,serif; Times New Roman=times new roman,times,serif; Courier New=courier new,courier,monospace; Helvetica=helvetica,arial,sans-serif; Verdana=verdana,geneva,sans-serif',
                    
                    font_size_formats: '10px 12px 14px 16px 18px 20px 24px 28px 32px 36px 48px',
                    
                    // Enhanced content styling
                    content_style: `
                        body {
                            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
                            font-size: 16px;
                            line-height: 1.6;
                            color: #333;
                        }
                        img.emoji {
                            width: 30px;
                            height: 30px;
                            vertical-align: middle;
                        }
                        h1 img.emoji, h2 img.emoji, h3 img.emoji {
                            position: relative;
                            top: 5px;
                            width: 42px;
                            height: 42px;
                        }
                        h4 img.emoji {
                            position: relative;
                            top: 3px;
                            width: 32px;
                            height: 32px;
                        }
                        blockquote {
                            border-left: 4px solid #ccc;
                            margin-left: 0;
                            padding-left: 16px;
                            font-style: italic;
                        }
                        pre {
                            background: #f4f4f4;
                            border: 1px solid #ddd;
                            border-radius: 4px;
                            padding: 16px;
                            overflow-x: auto;
                        }
                        table {
                            border-collapse: collapse;
                            width: 100%;
                        }
                        table td, table th {
                            border: 1px solid #ddd;
                            padding: 8px;
                        }
                        table th {
                            background-color: #f2f2f2;
                            font-weight: bold;
                        }
                    `,
                    
                    // Useful quick toolbars
                    quickbars_selection_toolbar: 'bold italic | quicklink h2 h3 blockquote | formatpainter',
                    quickbars_insert_toolbar: 'quickimage quicktable',
                    quickbars_image_toolbar: 'alignleft aligncenter alignright | imageoptions',
                    
                    // Essential table options
                    table_toolbar: 'tableprops tabledelete | tableinsertrowbefore tableinsertrowafter tabledeleterow | tableinsertcolbefore tableinsertcolafter tabledeletecol',
                    table_appearance_options: true,
                    table_grid: true,
                    table_resize_bars: true,
                    
                    // Image options
                    image_advtab: true,
                    image_caption: true,
                    image_description: true,
                    image_title: true,
                    
                    // Link options
                    link_title: true,
                    link_target_list: [
                        { title: 'None', value: '' },
                        { title: 'New window', value: '_blank' }
                    ],
                    
                    // Better paste handling
                    paste_as_text: false,
                    paste_auto_cleanup_on_paste: true,
                    
                    setup: (editor) => {
                        // Store reference to this component
                        const self = this;
                        let lastContent = '';
                        let changeTimeout;
                        
                        // Function to handle content changes
                        const handleContentChange = () => {
                            // Don't handle changes until fully initialized AND user has interacted
                            if (!self.isInitialized) {
                                return;
                            }
                            
                            clearTimeout(changeTimeout);
                            changeTimeout = setTimeout(() => {
                                const currentContent = editor.getContent();
                                if (currentContent !== lastContent) {
                                    lastContent = currentContent;
                                    
                                    // Call your change handler
                                    self.onContentChange(currentContent);
                                    
                                    // Also trigger change on the original textarea
                                    if (self.textarea) {
                                        self.textarea.value = currentContent;
                                        const event = new Event('change', { bubbles: true });
                                        self.textarea.dispatchEvent(event);
                                    }
                                }
                            }, 50);
                        };
                        
                        // Listen to user interaction events only (removed 'change' and 'setcontent')
                        editor.on('input keyup paste undo redo', handleContentChange);
                        editor.on('blur', handleContentChange); // Keep blur for when user finishes editing
                        
                        // Mark as initialized after first focus (user interaction)
                        editor.on('focus', () => {
                            if (!self.isInitialized) {
                                self.isInitialized = true;
                                lastContent = editor.getContent(); // Set baseline content
                            }
                            handleContentChange();
                        });
                        
                        // Custom emoji button setup
                        editor.ui.registry.addButton('customemoticons', {
                            text: 'Custom Emojis',
                            icon: 'emoji',
                            tooltip: 'Insert custom emoji',
                            onAction: function () {
                                resource.call().then(data => {
                                    let allEmojisHtml = data.map(emoji => 
                                        `<img loading="lazy" src="${emoji.src}" alt="${emoji.title}" 
                                         style="width: 32px; height: 32px; cursor: pointer; margin: 2px;" 
                                         data-emoji-src="${emoji.src}" data-emoji-title="${emoji.title}" 
                                         onclick="window.handleEmojiClick('${emoji.src}', '${emoji.title}');">`
                                    ).join("");

                                    const items = [{
                                        type: 'htmlpanel',
                                        html: `<div style="max-height: 300px; overflow-y: auto; padding: 10px;">${allEmojisHtml}</div>`,
                                        onPostRender: function (api) {
                                            api.element().addEventListener('click', function (e) {
                                                const src = e.target.getAttribute('data-emoji-src');
                                                const title = e.target.getAttribute('data-emoji-title');
                                                if (src && title) {
                                                    editor.insertContent(`<img src="${src}" class="emoji" alt="${title}" />`);
                                                    editor.windowManager.close();
                                                    handleContentChange();
                                                }
                                            });
                                        }
                                    }];

                                    editor.windowManager.open({
                                        title: translate('choose_an_emoji'),
                                        size: 'medium',
                                        body: {
                                            type: 'panel',
                                            items: items
                                        },
                                        buttons: [{
                                            type: 'cancel',
                                            text: translate('close')
                                        }]
                                    });
                                });
                            }
                        });
                    },
                    
                    // Simplified init_instance_callback
                    init_instance_callback: (editor) => {
                        const self = this;
                       
                        // Set initial value after a brief delay to ensure editor is ready
                        setTimeout(() => {
                            if (this._value) {
                                this.ignoreChange = true;
                                editor.setContent(this._value);
                                this.ignoreChange = false;
                            }
                        }, 100);
                    }
                });
                
                // Set initial value after initialization
                this.value = this._value;
            });
        });
    }

    onContentChange(val) {
        console.log('onContentChange called with:', val);
        if (!this.ignoreChange && this.isInitialized) {
            this.fire('change', val);
        }
        
        // Also update the textarea value to keep it in sync
        if (this.textarea) {
            this.textarea.value = val;
        }
    }

    get value() {
        if (window.tinymce) {
            const editor = tinymce.get(this.textarea_id);
            return editor ? editor.getContent() : this._value;
        } else {
            return this._value;
        }
    }

    set value(v) {
        if (v === undefined) {
            v = '';
        }
        this._value = v;
        
        // Update textarea
        if (this.textarea) {
            this.textarea.value = v;
        }
        
        // Update TinyMCE editor
        if (window.tinymce !== undefined) {
            let editor = tinymce.get(this.textarea_id);
            if (editor) {
                this.ignoreChange = true;
                editor.setContent(v);
                this.ignoreChange = false;
            }
        }
    }

    render() {
        return `<textarea id="${this.textarea_id}"></textarea>`;
    }
    
    afterRender() {
        this.textarea = document.getElementById(this.textarea_id);
        
        // Add direct change listener to textarea as backup
        if (this.textarea) {
            this.textarea.addEventListener('change', (e) => {
                if (this.isInitialized) {
                    this.onContentChange(e.target.value);
                }
            });
        }
    }

    destroy() {
        if (window.tinymce) {
            const editor = tinymce.get(this.textarea_id);
            if (editor) {
                editor.remove();
            }
        }
    }
}