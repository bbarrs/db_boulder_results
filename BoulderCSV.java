import java.io.*;
import java.util.ArrayList;

/*
* COSC 61: Final Project - Ben Barris and Daniel Lampert
* Java Class that parses a list of boulder results for pre-import data manipulation
 */

public class BoulderCSV {

    public static void main(String[] args) throws Exception
    {

        String inputCSV = "input/results_simple.csv";
        String outputCSV = "output/results_simple_decomp.csv";

        ArrayList<String> csvLines = new ArrayList<String>();
        // csv header
        csvLines.add("Qtops, Qzones, Qt_atts, Qz_atts, " +
                "Stops, Szones, St_atts, Sz_atts, Ftops, Fzones, Ft_atts, Fz_atts\n");

        // read input csv
        try
        {
            //parsing a CSV file into BufferedReader class constructor
            BufferedReader br = new BufferedReader(new FileReader(inputCSV));
            String line = br.readLine();
            int i = 0;
            while ((line = br.readLine()) != null)   //returns a Boolean value
            {
                String[] results = line.split(",");    // use comma as separator
                csvLines.add(commafy(parseLine(results)));
                i++;
                if (i >= 9741) {    // limit to only non-zero entries (total = 9741)
                    break;
                }
            }
        }
        catch (IOException e)
        {
            e.printStackTrace();
        }

        // write output to csv
        try (BufferedWriter writer = new BufferedWriter(new FileWriter(outputCSV))) {
            for (String s : csvLines) {
                writer.write(s);
            }
        }
        catch (IOException e)
        {
            e.printStackTrace();
        }

    }

    // take an int array for a row and format it for csv entry
    public static String commafy(int[] row) {
        String s = "";
        for (int i = 0; i < row.length-1; i++) {
            s += row[i];
            s += ", ";
        }
        s += row[row.length-1] + "\n";
        return s;
    }

    // parse qualification, semis, and finals results into int[]
    public static int[] parseLine (String[] s) {
        int[] row = new int[12];
        if (s.equals(null)) {
            return null;
        }
        for (int i = 0; i < s.length; i++) {
            if (s[i] != null && s[i].length() >0) {
                int[] result = parseResult(s[i]);
                System.arraycopy(parseResult(s[i]),0, row, i*4,  result.length);
            }
        }
        return row;
    }

    // parse an individual result string eg. "2T4z27"
    public static int[] parseResult(String s) {
        int[] results = new int[4];
        if (s.equals(null)) {
            return null;
        }

        int tempIdx = 0;
        boolean lastPart = false;

        for (int i = 0; i < s.length(); i++) {
            if (s.charAt(i) == 'T') { // grab # of tops
                results[0] = Integer.parseInt(s.substring(0,i));
                tempIdx = i;
            }
            else if (s.charAt(i) == 'z') { // grab # of zones
                results[1] = Integer.parseInt(s.substring(tempIdx+1,i));
                tempIdx = i;
                lastPart = true;
            }
            else if (lastPart) { // grab # of top and zone attempts
                String attempts = s.substring(tempIdx+1);
                if (attempts.length() == 2) {
                    results[2] = Integer.parseInt(attempts.substring(0,1));
                    results[3] = Integer.parseInt(attempts.substring(1,2));
                } else if (attempts.length() == 3) {
                    int ta12 = Integer.parseInt(attempts.substring(0,1));
                    int za12 = Integer.parseInt(attempts.substring(1,3));

                    if (ta12 >= results[0] && za12 >= results[1]) {
                        results[2] = ta12;
                        results[3] = za12;
                    } else {
                        results[2] = Integer.parseInt(attempts.substring(0, 2));
                        results[3] = Integer.parseInt(attempts.substring(2, 3));

                    }
                } else if (attempts.length() == 4) {
                    results[2] = Integer.parseInt(attempts.substring(0,2));
                    results[3] = Integer.parseInt(attempts.substring(2,4));
                }
                break;
            }
        }
        return results;
    }

}
