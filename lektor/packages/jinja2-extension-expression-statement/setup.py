from setuptools import setup

setup(
    name='lektor-jinja2-extension-expression-statement',
    version='0.1',
    author='Maarten',
    author_email='anonymous.maarten@gmail.com',
    license='MIT',
    py_modules=['lektor_jinja2_extension_expression_statement'],
    entry_points={
        'lektor.plugins': [
            'jinja2-extension-expression-statement = lektor_jinja2_extension_expression_statement:Jinja2ExtensionExpressionStatementPlugin',
        ]
    }
)
