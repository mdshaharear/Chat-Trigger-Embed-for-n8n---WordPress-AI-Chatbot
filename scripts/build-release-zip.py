#!/usr/bin/env python3
from __future__ import annotations

import argparse
import json
import sys
from pathlib import Path
from zipfile import ZIP_DEFLATED, ZipFile


EXCLUDED_SUFFIXES = {'.log', '.webm', '.png', '.jpg', '.jpeg', '.gif', '.bmp', '.zip'}
INCLUDE_ROOTS = [
    'admin',
    'includes',
    'dist',
    'src',
    'tools',
]
INCLUDE_FILES = [
    'chat-trigger-embed-for-n8n.php',
    'LICENSE',
    'uninstall.php',
    'readme.txt',
    'CHANGELOG.md',
    'THIRD_PARTY_NOTICES.md',
    'package.json',
    'package-lock.json',
    'eslint.config.js',
    'tsconfig.json',
    'vite.config.js',
    'vitest.config.ts',
]


def load_package_name(root: Path) -> str:
    package_json = json.loads((root / 'package.json').read_text(encoding='utf-8'))
    return str(package_json.get('name', 'chat-trigger-embed-for-n8n'))


def iter_files(root: Path):
    for relative in INCLUDE_FILES:
        path = root / relative
        if path.is_file():
            yield path

    for relative in INCLUDE_ROOTS:
        path = root / relative
        if not path.exists():
            continue
        if path.is_file():
            yield path
            continue
        for file_path in path.rglob('*'):
            if not file_path.is_file():
                continue
            if file_path.suffix.lower() in EXCLUDED_SUFFIXES:
                continue
            yield file_path


def build_zip(root: Path, output: Path) -> None:
    package_name = load_package_name(root)
    output.parent.mkdir(parents=True, exist_ok=True)
    if output.exists():
        output.unlink()

    written = 0
    with ZipFile(output, 'w', compression=ZIP_DEFLATED, compresslevel=9) as archive:
        for file_path in iter_files(root):
            relative = file_path.relative_to(root).as_posix()
            archive.write(file_path, arcname=f'{package_name}/{relative}')
            written += 1

    print(f'Created {output} with {written} files.')


def main(argv: list[str]) -> int:
    parser = argparse.ArgumentParser(description='Build a WordPress plugin release ZIP.')
    parser.add_argument('--root', default=Path(__file__).resolve().parents[1], type=Path)
    parser.add_argument('--output', required=True, type=Path)
    args = parser.parse_args(argv)

    build_zip(args.root.resolve(), args.output.resolve())
    return 0


if __name__ == '__main__':
    raise SystemExit(main(sys.argv[1:]))
