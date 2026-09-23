#!/usr/bin/env python3

import sys
import pikepdf


def unlock_pdf(input_file, output_file, password):

    try:

        # Open encrypted PDF using supplied password
        pdf = pikepdf.open(
            input_file,
            password=password
        )

        # Save a new PDF without encryption
        pdf.save(output_file)

        pdf.close()

        print("SUCCESS")
        sys.exit(0)

    except pikepdf.PasswordError:
        print("INVALID_PASSWORD")
        sys.exit(2)

    except Exception as e:
        print("ERROR:" + str(e))
        sys.exit(1)


if __name__ == "__main__":

    if len(sys.argv) != 4:

        print(
            "Usage: unlock_pdf.py "
            "input.pdf output.pdf password"
        )

        sys.exit(1)

    input_file = sys.argv[1]
    output_file = sys.argv[2]
    password = sys.argv[3]

    unlock_pdf(
        input_file,
        output_file,
        password
    )