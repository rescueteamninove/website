from collections import namedtuple
import datetime
import typing

from lektor.pluginsystem import Plugin
from lektor.sourceobj import SourceObject
import jinja2.ext
import lektor.context
from lektor.db import F

Activity = namedtuple('Activity', ('description', 'price', ))


class PersonTool(object):
    @staticmethod
    def _ctx_get_email(ctx, person):
        ctx = lektor.context.get_ctx()
        res = ctx.pad.databags.lookup('person.{}.email'.format(person))
        if res is None:
            raise RuntimeError('Illegal person: {}'.format(person))
        return res

    @classmethod
    def get_email(cls, person):
        ctx = lektor.context.get_ctx()
        return cls._ctx_get_email(ctx, person)

    @classmethod
    def get_emails(cls, persons):
        ctx = lektor.context.get_ctx()
        return list(cls._ctx_get_email(ctx, person) for person in persons)


class DateTimeTool(object):
    @staticmethod
    def date_MONTH_tostr(month_int: int) -> str:
        ctx = lektor.context.get_ctx()
        locale = lektor.context.get_locale()
        return ctx.pad.databags.lookup('i18n.{}.month_{}'.format(locale, month_int))

    @classmethod
    def date_D_M_YYYY_tostr(cls, d: datetime.date) -> str:
        return '{} {} {:04}'.format(d.day, cls.date_MONTH_tostr(d.month), d.year)

    @classmethod
    def date_DDMMYYYY_tostr(cls, d: datetime.date) -> str:
        return '{}/{}/{:04}'.format(d.day, d.month, d.year)

    @classmethod
    def time_HuMM_tostr(cls, dt: datetime.datetime) -> str:
        return '{}u{:02}'.format(dt.hour, dt.minute)

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

    def get_club_activities(self) -> typing.Dict[str, Activity]:
        ctx = lektor.context.get_ctx()

        activities = dict()

        label_member = ctx.pad.get('/lidmaatschap')['label_enlist']
        activities['lid'] = (Activity(
            description=label_member,
            price=ctx.pad.databags.lookup('club.rescueteam_ninove.subscription_fee'))
        )

        label_education = ctx.pad.get('/opleiding/cursus')['label_enlist']
        for r_i, child in enumerate(ctx.pad.get('/opleiding/cursus').children.filter(F.registration_open)):
            year = child['date_start'].year
            price = child['price']
            activities['ro{}'.format(r_i)] = Activity(
                description='{} {}'.format(label_education, year),
                price=price
            )

        label_reeducation = ctx.pad.get('/opleiding/bijscholing')['label_enlist']
        for r_i, child in enumerate(ctx.pad.get('/opleiding/bijscholing').children.filter(F.registration_open)):
            date = DateTimeTool.date_DDMMYYYY_tostr(child['datetime_start'])
            price = child['price']
            activities['bs{}'.format(r_i)] = Activity(
                description='{} {}'.format(label_reeducation, date),
                price=price,
            )

        return activities

    @staticmethod
    def get_generator_name_version():
        from lektor.cli import version
        return 'Lektor {}'.format(version)

    def on_setup_env(self, **extra):
        self.env.jinja_env.add_extension(jinja2.ext.do)
        self.env.jinja_env.globals.update(
            zip=zip,
            get_club_activities=self.get_club_activities,
            date_tool=DateTimeTool,
            person_tool=PersonTool,
            generator_name_version=self.get_generator_name_version(),
        )
        self.env.jinja_env.filters.update(
            qescape=lambda s : s.replace('"', '\"'),
        )
