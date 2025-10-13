<?php

namespace CBCGames\Infrastructure\Repository\Quiz;

use CBCGames\Domain\Quiz\Question;
use CBCGames\Domain\Quiz\QuestionRepository;

class InMemoryQuestionRepository implements QuestionRepository
{
    private $questions;

    public function __construct()
    {
        $q = [];
        $add = function ($question, $options, $answer) use (&$q) {
            $q[] = new Question($question, $options, $answer);
        };

        // Questions from original quiz-game.html
        $add("Where is the DA-Crop Biotechnology Center (DA-CBC) primarily located?", ["Los Baños, Laguna", "Science City of Muñoz, Nueva Ecija", "Quezon City", "Davao City"], "Science City of Muñoz, Nueva Ecija");
        $add("The DA-CBC is situated within the complex of which renowned agricultural institution?", ["University of the Philippines Los Baños", "International Rice Research Institute (IRRI)", "Philippine Rice Research Institute (PhilRice)", "Central Luzon State University (CLSU)"], "Philippine Rice Research Institute (PhilRice)");
        $add("What is a major goal of the DA-CBC?", ["To regulate organic farming standards", "To develop high-yielding and climate-resilient crops", "To market agricultural products internationally", "To exclusively study animal genetics"], "To develop high-yielding and climate-resilient crops");
        $add("Which Vitamin A-enriched GM rice was launched for commercial use during the DA-CBC's inauguration?", ["Miracle Rice", "Heirloom Rice", "Iron-Fortified Rice", "Golden Rice"], "Golden Rice");
        $add("Besides rice, the DA-CBC also conducts vital research on other crops except:", ["Corn", "Coconut", "Banana", "Wheat"], "Wheat");
        $add("The DA-CBC is described as the '______' for increasing crop productivity in the Philippines.", ["Main Hub", "Ground Zero", "Control Center", "Final Frontier"], "Ground Zero");

        $add("What does GMO stand for?", ["Genetically Modified Organism", "Generally Marketed Organism", "Global Farming Opportunity", "Greater Microbial Output"], "Genetically Modified Organism");
        $add("What is the scientific process of inserting a gene from one organism into another called?", ["Cross-pollination", "Genetic Engineering", "Hybridization", "Photosynthesis"], "Genetic Engineering");
        $add("Bt corn is a well-known GM crop that is genetically modified to be resistant to what?", ["Drought", "Herbicides", "Specific insect pests like the corn borer", "Fungal diseases"], "Specific insect pests like the corn borer");
        $add("The method of growing plant cells, tissues, or organs in a sterile, artificial nutrient medium is called:", ["Hydroponics", "Tissue Culture", "Aeroponics", "Grafting"], "Tissue Culture");
        $add("What does DNA, the molecule of life, stand for?", ["Deoxyribonucleic acid", "Dynamic natural allele", "Di-nuclear acid", "Deoxyribo nucleotide"], "Deoxyribonucleic acid");
        $add("Golden Rice is a GM crop developed to combat Vitamin A deficiency by producing what?", ["Retinol", "Beta-carotene", "Ascorbic acid", "Folic acid"], "Beta-carotene");
        $add("What is the primary purpose of developing herbicide-tolerant crops?", ["To make them grow faster", "To improve their taste", "To kill weeds without harming the crop", "To make them more colorful"], "To kill weeds without harming the crop");
        $add("What is CRISPR-Cas9?", ["A type of fertilizer", "A harvesting machine", "A powerful gene-editing tool", "A brand of pesticide"], "A powerful gene-editing tool");
        $add("The 'Flavr Savr' tomato, one of the first GM foods available, was modified for what trait?", ["Brighter red color", "Insect resistance", "Delayed ripening", "Sweeter taste"], "Delayed ripening");
        $add("An organism that contains genetic material from another species is called:", ["A hybrid", "A clone", "A mutant", "Transgenic"], "Transgenic");
        $add("In genetic engineering, what is often used to carry a new gene into a plant cell?", ["A virus", "A specialized bacterium (Agrobacterium)", "A microscopic needle", "A water-based solution"], "A specialized bacterium (Agrobacterium)");
        $add("What is 'bioinformatics'?", ["The study of life in different environments", "The use of computers to analyze biological data like DNA", "A type of organic farming", "The marketing of biological products"], "The use of computers to analyze biological data like DNA");
        $add("Marker-assisted selection (MAS) is a technique that uses DNA markers to:", ["Label plants in a field", "Help breeders select desirable traits more efficiently", "Identify ripe fruit", "Detect pests in the soil"], "Help breeders select desirable traits more efficiently");
        $add("Why is genetic diversity crucial for agriculture?", ["It ensures all plants look identical", "It provides the raw material for breeding new, resilient varieties", "It simplifies the harvesting process", "It is not important"], "It provides the raw material for breeding new, resilient varieties");
        $add("What is a small, circular piece of DNA often used in genetic engineering to transfer genes?", ["Chromosome", "Ribosome", "Plasmid", "Mitochondria"], "Plasmid");
        $add("Which of these is a potential benefit of crop biotechnology?", ["Reduced need for pesticides", "Increased crop yields", "Enhanced nutritional value", "All of the above"], "All of the above");
        $add("The main role of a 'promoter' sequence in a gene construct is to:", ["Stop the gene from working", "Tell the cell when and where to express the gene", "Make the gene visible", "Help the gene replicate"], "Tell the cell when and where to express the gene");
        $add("Which government agency in the Philippines is heavily involved in crop biotechnology research and development?", ["Department of Tourism", "Department of Agriculture", "Department of Education", "Department of Health"], "Department of Agriculture");
        $add("The development of drought-tolerant crops is an example of biotechnology addressing what major global issue?", ["Food waste", "Climate Change", "Air pollution", "Soil erosion"], "Climate Change");
        $add("What is the term for the entire genetic material of an organism?", ["Genome", "Proteome", "Phenotype", "Genotype"], "Genome");
        $add("True or False: Biotechnology in agriculture is a modern invention and was not used before the discovery of DNA.", ["True", "False"], "False");
        $add("Improving the nutritional content of a crop, such as adding vitamins, is known as:", ["Bio-fortification", "Bio-magnification", "Bio-remediation", "Bio-degradation"], "Bio-fortification");

        $this->questions = $q;
    }

    public function all()
    {
        return $this->questions;
    }
}
