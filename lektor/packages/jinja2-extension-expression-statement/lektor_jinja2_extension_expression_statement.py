from lektor.pluginsystem import Plugin
from lektor.sourceobj import SourceObject
import jinja2.ext


class Jinja2ExtensionExpressionStatementPlugin(Plugin):
    name = 'lektor-jinja2-extension-expression-statement'
    description = 'Add the "do" expression-statement. ' \
                  'The “do” aka expression-statement extension adds a simple do tag to the template engine ' \
                  'that works like a variable expression but ignores the return value.'

    def on_process_template_context(self, context, **extra):
        def get_parents(source_this: SourceObject):
            pad_this = source_this.pad
            record_root = pad_this.get_root(alt=source_this.alt)
            records_parents = []
            record_parent = record_root
            while True:
                records_parents.append(record_parent)
                if source_this.record.path == record_parent.path:
                    break
                for record_child in record_parent.children:
                    if source_this.is_child_of(record_child):
                        record_parent = record_child
                        break
                else:
                    return None
            return records_parents

        context['get_parents'] = get_parents

    def on_setup_env(self, **extra):
        self.env.jinja_env.add_extension(jinja2.ext.do)
