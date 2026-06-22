/**
 * 富文本编辑器（Jodit MIT + 资源库选图）
 * 依赖：Vue、ElementPlus、axios（neoAxiosSetup）、jodit.min.js、jodit.min.css、neo-upload.js
 *
 * 用法：
 *   <neo-richtext v-model="html" :upload-url="uploadUrl" :storage-public-base="storageBase" />
 * 自定义工具栏（可选）：
 *   :toolbar-buttons="['bold','italic','|','source']"
 *   :toolbar-adaptive="true"  开启后窄屏会收入三点菜单
 */
(function (global) {
  let editorSeq = 0;

  function debounce(fn, wait) {
    let t;
    return function () {
      const ctx = this;
      const args = arguments;
      clearTimeout(t);
      t = setTimeout(function () {
        fn.apply(ctx, args);
      }, wait);
    };
  }

  function ensureJoditAssets(base) {
    const root = (base || '/assets/admin-static/jodit').replace(/\/$/, '');
    if (!document.getElementById('neo-jodit-styles')) {
      const link = document.createElement('link');
      link.id = 'neo-jodit-styles';
      link.rel = 'stylesheet';
      link.href = root + '/jodit.min.css';
      document.head.appendChild(link);
    }
    ensureNeoRichtextEditorStyles();
    return root;
  }

  function ensureNeoRichtextEditorStyles() {
    if (document.getElementById('neo-richtext-editor-styles')) return;
    const style = document.createElement('style');
    style.id = 'neo-richtext-editor-styles';
    style.textContent = [
      '.neo-richtext-field .jodit-wysiwyg blockquote {',
      '  margin: 0.75em 0;',
      '  padding: 0.5em 1em;',
      '  border-left: 4px solid #dcdfe6;',
      '  color: #606266;',
      '  background: #f5f7fa;',
      '}',
      '.neo-richtext-field .jodit-wysiwyg pre {',
      '  margin: 0.75em 0;',
      '  padding: 0.75em 1em;',
      '  border-radius: 4px;',
      '  background: #282c34;',
      '  color: #abb2bf;',
      '  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;',
      '  font-size: 13px;',
      '  line-height: 1.6;',
      '  white-space: pre-wrap;',
      '  overflow-x: auto;',
      '}',
      '.neo-richtext-field .jodit-wysiwyg pre code {',
      '  font-family: inherit;',
      '  font-size: inherit;',
      '}',
    ].join('\n');
    document.head.appendChild(style);
  }

  function normalizeFormatBlock(editor, tag) {
    if (!editor || !tag) return;
    const root = editor.editor;
    const current = editor.s.current();
    if (!current || !root || !root.contains(current)) return;

    let block = current.nodeType === 1 ? current : current.parentElement;
    while (block && block !== root) {
      if (block.nodeType === 1 && block.nodeName.toLowerCase() === tag) {
        block.style.fontSize = '';
        block.style.fontWeight = '';
        block.style.fontFamily = '';
        if (tag === 'pre') {
          block.style.whiteSpace = 'pre-wrap';
        }
        return;
      }
      block = block.parentElement;
    }
  }

  function buildDefaultToolbar(self) {
    return [
      'font',
      'fontsize',
      'bold',
      'italic',
      'underline',
      'strikethrough',
      '|',
      'brush',
      '|',
      'ul',
      'ol',
      '|',
      'link',
      {
        name: 'neoimage',
        icon: 'image',
        tooltip: '从资源库选择图片',
        exec: function () {
          self.openImagePicker();
        },
      },
      'table',
      'symbols',
      '|',
      'eraser',
      'source',
    ];
  }

  global.NeoRichtext = {
    name: 'neo-richtext',
    components: {
      NeoUploadField: global.NeoUploadField,
    },
    props: {
      modelValue: { type: String, default: '' },
      uploadUrl: { type: String, required: true },
      resourceListUrl: { type: String, default: '/admin/api/resources' },
      storagePublicBase: { type: String, default: '' },
      uploadScene: { type: String, default: 'richtext' },
      height: { type: Number, default: 500 },
      minHeight: { type: Number, default: 500 },
      placeholder: { type: String, default: '在此编辑…' },
      joditBase: { type: String, default: '/assets/admin-static/jodit' },
      /** 自定义工具栏按钮，格式同 Jodit buttons；不传则用默认 */
      toolbarButtons: { type: Array, default: null },
      /** false = 全部按钮固定显示在工具栏，不收入三点菜单 */
      toolbarAdaptive: { type: Boolean, default: false },
      disabled: { type: Boolean, default: false },
    },
    emits: ['update:modelValue', 'ready'],
    data() {
      editorSeq += 1;
      return {
        editorId: 'neo-richtext-' + editorSeq,
        editor: null,
        editorReady: false,
        imagePickPath: '',
        syncingFromParent: false,
      };
    },
    watch: {
      modelValue(val) {
        if (!this.editorReady || !this.editor) return;
        const next = val == null ? '' : String(val);
        if (this.editor.value !== next) {
          this.syncingFromParent = true;
          this.editor.value = next;
          this.syncingFromParent = false;
        }
      },
      disabled(val) {
        if (this.editor) {
          this.editor.setReadOnly(!!val);
        }
      },
    },
    mounted() {
      ensureJoditAssets(this.joditBase);
      this.$nextTick(() => this.initEditor());
    },
    beforeUnmount() {
      this.destroyEditor();
    },
    methods: {
      destroyEditor() {
        if (this.editor) {
          this.editor.destruct();
          this.editor = null;
          this.editorReady = false;
        }
      },
      emitContent() {
        if (this.syncingFromParent || !this.editor) return;
        this.$emit('update:modelValue', this.editor.value);
      },
      getContent() {
        if (!this.editor) {
          return this.modelValue == null ? '' : String(this.modelValue);
        }
        return this.editor.value;
      },
      setContent(html) {
        const val = html == null ? '' : String(html);
        if (this.editor) {
          this.syncingFromParent = true;
          this.editor.value = val;
          this.syncingFromParent = false;
        }
        this.$emit('update:modelValue', val);
      },
      resolvePublicUrl(path) {
        const v = String(path || '').trim();
        if (!v) return '';
        if (v.startsWith('http://') || v.startsWith('https://') || v.startsWith('/')) {
          return v;
        }
        const base = (this.storagePublicBase || '').replace(/\/$/, '');
        if (base) return base + '/' + v.replace(/^\/+/, '');
        return '/storage/' + v.replace(/^\/+/, '');
      },
      openImagePicker() {
        const picker = this.$refs.imagePicker;
        if (picker && typeof picker.openPicker === 'function') {
          picker.openPicker();
        }
      },
      onImagePicked(row) {
        if (!this.editor || !row) return;
        const url = row.public_url || this.resolvePublicUrl(row.storage_path);
        if (!url) {
          ElementPlus.ElMessage.warning('无法解析图片地址');
          return;
        }
        const alt = row.original_name || '';
        if (this.editor.s && typeof this.editor.s.insertImage === 'function') {
          this.editor.s.insertImage(url, null, alt);
        } else {
          this.editor.s.insertHTML(
            '<img src="' + url.replace(/"/g, '&quot;') + '" alt="' + String(alt).replace(/"/g, '&quot;') + '" />'
          );
        }
        this.imagePickPath = '';
        this.emitContent();
      },
      initEditor() {
        const self = this;
        if (typeof global.Jodit === 'undefined') {
          ElementPlus.ElMessage.error('富文本脚本加载失败，请先引入 jodit.min.js');
          return;
        }
        if (!global.NeoUploadField) {
          ElementPlus.ElMessage.error('富文本依赖 neo-upload.js，请先引入');
          return;
        }
        const host = this.$refs.editorHost;
        if (!host) return;

        const debouncedEmit = debounce(function () {
          self.emitContent();
        }, 200);

        const toolbarButtons = Array.isArray(this.toolbarButtons) && this.toolbarButtons.length
          ? this.toolbarButtons
          : buildDefaultToolbar(self);

        this.editor = global.Jodit.make(host, {
          height: this.height,
          minHeight: this.minHeight,
          language: 'zh_cn',
          placeholder: this.placeholder,
          readonly: this.disabled,
          hidePoweredByJodit: true,
          showCharsCounter: true,
          showWordsCounter: true,
          showXPathInStatusbar: false,
          enableDragAndDropFileToEditor: false,
          askBeforePasteHTML: false,
          askBeforePasteFromWord: false,
          toolbarAdaptive: this.toolbarAdaptive,
          buttons: toolbarButtons,
          buttonsMD: toolbarButtons,
          buttonsSM: toolbarButtons,
          buttonsXS: toolbarButtons,
          removeButtons: ['image', 'file', 'video', 'about', 'fullsize', 'print', 'preview'],
          disablePlugins: ['file', 'video', 'media', 'iframe'],
          uploader: {
            insertImageAsBase64URI: false,
            url: '',
          },
          filebrowser: {
            ajax: { url: '' },
          },
          allowResizeTags: new Set(['img', 'iframe', 'table', 'jodit']),
          resizer: {
            useAspectRatio: new Set(['img']),
            forImageChangeAttributes: true,
          },
          events: {
            afterInit: function (editor) {
              self.editorReady = true;
              const initial = self.modelValue == null ? '' : String(self.modelValue);
              if (initial) {
                self.syncingFromParent = true;
                editor.value = initial;
                self.syncingFromParent = false;
              }
              self.$emit('ready', editor);
            },
            afterCommitStyle: function (commitStyle) {
              const tag = commitStyle && commitStyle.options && commitStyle.options.element;
              if (tag === 'blockquote' || tag === 'pre' || /^h[1-6]$/.test(tag || '')) {
                normalizeFormatBlock(self.editor, tag);
              }
            },
            change: debouncedEmit,
            blur: debouncedEmit,
          },
        });
      },
    },
    template: `
      <div class="neo-richtext-field" style="width:100%;">
        <NeoUploadField
          v-if="uploadUrl"
          ref="imagePicker"
          v-model="imagePickPath"
          silent
          image-only
          pick-from-library
          :action="uploadUrl"
          :scene="uploadScene"
          accept="image/jpeg,image/png,image/gif,image/webp"
          :storage-base="storagePublicBase"
          :resource-list-url="resourceListUrl"
          @picked="onImagePicked"
        />
        <div class="neo-richtext-editor">
          <textarea ref="editorHost" :id="editorId"></textarea>
        </div>
      </div>
    `,
  };
})(window);
