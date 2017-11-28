#!/usr/bin/env python3

import argparse
import git
from pathlib import Path
import lektor
import lektor.project
import lektor.cli
import sys
import time
import typing


class Builder(object):
    def __init__(self, src: Path, dst: Path, build_flags: typing.List[str]):
        self._src = src
        self._dst = dst
        self._build_flags = build_flags

        self._verbosity = 0
        self._ctx = lektor.cli.Context()
        self._ctx.set_project_path(self._src)
        # self._project = self.__create_project()

    # def __create_project(self) -> lektor.project.Project:
    #     return lektor.project.Project.discover(base=self._src)

    @property
    def _buildstate(self) -> Path:
        return None

    @property
    def _buildflags(self) -> object:
        return self._build_flags

    def build(self):
        from lektor.builder import Builder
        from lektor.reporter import CliReporter

        self._ctx.load_plugins()

        env = self._ctx.get_env()

        def _build():
            builder = Builder(env.new_pad(), str(self._dst),
                              buildstate_path=self._buildstate,
                              build_flags=self._buildflags)
            failures = builder.build_all()
            builder.prune()
            return failures == 0

        reporter = CliReporter(env)
        with reporter:
            success = _build()

        return success

    def clean(self):
        from lektor.builder import Builder
        from lektor.reporter import CliReporter

        # self._ctx.load_plugins()
        env = self._ctx.get_env()

        reporter = CliReporter(env)  # (env, verbosity=self._verbosity)
        with reporter:
            builder = Builder(env.new_pad(), self._dst)
            builder.prune(all=True)


def main():
    DEFAULT_GIT_REPO = 'git@github.com:madebr/rescueteam.git'

    parser = argparse.ArgumentParser()
    parser.add_argument('-i', type=Path, dest='src', metavar='PATH', help='Input, path of Lektor project')
    parser.add_argument('-o', type=Path, dest='dst', metavar='PATH', help='Output, path of website')
    parser.add_argument('-f', type=str, dest='build_flags', nargs=argparse.ZERO_OR_MORE, default=None, metavar='PATH', help='Build flag(s)')
    parser.add_argument('-r', type=str, dest='repo', default=DEFAULT_GIT_REPO, metavar='REPO', help='Remote git repository')
    parser.add_argument('action', choices=('build-init', 'rebuild', 'build-commit', 'build-push', ), help='Action to perform')
    ns = parser.parse_args()
    if ns.src is None:
        ns.src = Path(__file__).absolute().parent / 'lektor'
    if ns.dst is None:
        ns.dst = Path(__file__).absolute().parent / 'build'
    if ns.build_flags is None:
        ns.build_flags = ('webpack', )

    ns.dst.mkdir(exist_ok=True)

    b = Builder(ns.src, ns.dst, ns.build_flags)
    if ns.action == 'build-init':
        r = git.Repo.init(ns.dst)
        github = r.create_remote('github', ns.repo)
        github.fetch()

        gh_pages = r.create_head('gh-pages', github.refs['gh-pages'])
        gh_pages.set_tracking_branch(github.refs['gh-pages'])
        gh_pages.checkout()
        success = True
    elif ns.action == 'rebuild':
        r = git.Repo(str(ns.dst))
        gh_pages = r.branches['gh-pages']
        gh_pages.checkout()
        b.clean()
        success = b.build()
    elif ns.action == 'build-commit':
        r = git.Repo(str(ns.dst))
        # gh_pages = r.branches['gh-pages']
        # gh_pages.checkout()
        r.git.checkout('gh-pages')
        r.git.add(all=True)
        m = time.strftime('Commit on %Y-%m-%d %H:%M:%S')
        r.index.commit(m)
        success = True
    elif ns.action == 'build-push':
        r = git.Repo(str(ns.dst))
        gh_pages = r.branches['gh-pages']
        gh_pages.checkout()

        github = r.remotes.github
        github.push()
        success = True
    else:
        success = False

    if not success:
        print('ERROR!', file=sys.stderr)

    sys.exit(0 if success else 1)

if __name__ == '__main__':
    main()
