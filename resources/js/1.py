import os

def escape(name: str) -> str:
    return name.replace('"', '\\"')

def generate_graphviz(root_dir: str, output_file: str = "tree.dot"):
    with open(output_file, "w", encoding="utf-8") as f:
        f.write('digraph G {\n')
        f.write('    graph [rankdir=LR];\n')
        f.write('    node [shape=box, fontname="Arial"];\n')

        for dirpath, dirnames, filenames in os.walk(root_dir):
            parent = escape(os.path.basename(dirpath)) or root_dir

            # Узел директории
            f.write(f'    "{dirpath}" [label="{escape(os.path.basename(dirpath)) or "."}"];\n')

            # Поддиректории
            for d in dirnames:
                child_path = os.path.join(dirpath, d)
                f.write(f'    "{dirpath}" -> "{child_path}";\n')
                f.write(f'    "{child_path}" [label="{escape(d)}"];\n')

            # Файлы
            for file in filenames:
                file_path = os.path.join(dirpath, file)
                f.write(f'    "{dirpath}" -> "{file_path}";\n')
                f.write(f'    "{file_path}" [label="{escape(file)}", shape=note];\n')

        f.write("}\n")

    print(f"DOT‑файл успешно создан: {output_file}")


if __name__ == "__main__":
    generate_graphviz(".")
